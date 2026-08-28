<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use App\Models\ConcatSectorRoleMapping;
use App\Models\ConcatUserRoleGrant;
use App\Models\Sector;
use App\Services\ConcatService;
use App\Services\ConcatSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ConcatController extends Controller
{
    public function __construct(private ConcatService $concat, private ConcatSyncService $syncService) {}

    /**
     * Show the sector → ConCat role mapping management page.
     */
    public function index(): View|RedirectResponse
    {
        if (! $this->concat->isConfigured()) {
            return redirect()->route('settings.index')
                ->with('error', 'Connect ConCat under External Integrations before managing sector mappings.');
        }

        $sectors = Sector::with('concatRoleMapping')->orderBy('name')->get();

        $roles = collect(Cache::remember('concat_roles', 300, fn () => $this->concat->getRoles() ?? []));

        $grantCounts = ConcatUserRoleGrant::selectRaw('sector_id, count(*) as total')
            ->groupBy('sector_id')
            ->pluck('total', 'sector_id');

        $lastSyncedAt = ApplicationSetting::get('concat_last_synced_at');

        return view('admin.concat.index', compact('sectors', 'roles', 'grantCounts', 'lastSyncedAt'));
    }

    /**
     * Save which sectors are watched and which ConCat role each maps to.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sectors' => 'array',
            'sectors.*.watched' => 'boolean',
            'sectors.*.concat_role_id' => 'nullable|string',
            'sectors.*.concat_scope' => 'nullable|string|in:convention,global',
        ]);

        $roles = collect(Cache::remember('concat_roles', 300, fn () => $this->concat->getRoles() ?? []))
            ->keyBy('id');

        foreach ($validated['sectors'] ?? [] as $sectorId => $input) {
            $sector = Sector::find($sectorId);
            if (! $sector) {
                continue;
            }

            $watched = ! empty($input['watched']) && ! empty($input['concat_role_id']);

            if ($watched) {
                $role = $roles->get($input['concat_role_id']);

                ConcatSectorRoleMapping::updateOrCreate(
                    ['sector_id' => $sector->id],
                    [
                        'concat_role_id' => $input['concat_role_id'],
                        'concat_role_name' => $role['name'] ?? $input['concat_role_id'],
                        'concat_scope' => $input['concat_scope'] ?? 'convention',
                    ]
                );

                $this->syncService->syncSector($sector->fresh('concatRoleMapping'));
            } else {
                $mapping = ConcatSectorRoleMapping::where('sector_id', $sector->id)->first();
                if ($mapping) {
                    $this->syncService->revokeAllForSector($sector);
                    $mapping->delete();
                }
            }
        }

        return redirect()->route('admin.concat.index')
            ->with('success', ['message' => 'ConCat sector mappings saved.']);
    }

    /**
     * Run a manual reconciliation pass across every watched sector.
     */
    public function sync(): RedirectResponse
    {
        $result = $this->syncService->syncAll();

        return redirect()->route('admin.concat.index')
            ->with('success', [
                'message' => "Sync complete: {$result['granted']} granted, {$result['revoked']} revoked, {$result['unmatched']} unmatched.",
            ]);
    }
}

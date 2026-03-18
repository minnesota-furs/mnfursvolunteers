<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FiscalLedger;
use App\Models\Shift;
use App\Models\User;
use App\Models\VolunteerHours;
use App\Models\VolunteerPerk;
use App\Models\VolunteerPerkSet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerPerkSetController extends Controller
{
    public function index(): View
    {
        $sets = VolunteerPerkSet::withCount('perks')
            ->with('fiscalLedger')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.perk-sets.index', compact('sets'));
    }

    public function create(): View
    {
        $fiscalLedgers = FiscalLedger::orderBy('start_date', 'desc')->get();

        return view('admin.perk-sets.create', compact('fiscalLedgers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'fiscal_ledger_id' => ['nullable', 'exists:fiscal_ledgers,id'],
            'visible_from'     => ['nullable', 'date'],
            'visible_until'    => ['nullable', 'date', 'after_or_equal:visible_from'],
            'is_active'        => ['sometimes', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
        ]);

        $set = VolunteerPerkSet::create([
            'name'             => $validated['name'],
            'description'      => $validated['description'] ?? null,
            'fiscal_ledger_id' => $validated['fiscal_ledger_id'] ?? null,
            'visible_from'     => $validated['visible_from'] ?? null,
            'visible_until'    => $validated['visible_until'] ?? null,
            'is_active'        => $request->boolean('is_active', true),
            'sort_order'       => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.perk-sets.index')
            ->with('success', ['message' => 'Perk set <span class="font-semibold">' . e($set->name) . '</span> created successfully.']);
    }

    public function edit(VolunteerPerkSet $perkSet): View
    {
        $fiscalLedgers = FiscalLedger::orderBy('start_date', 'desc')->get();

        return view('admin.perk-sets.create', compact('perkSet', 'fiscalLedgers'));
    }

    public function update(Request $request, VolunteerPerkSet $perkSet): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'fiscal_ledger_id' => ['nullable', 'exists:fiscal_ledgers,id'],
            'visible_from'     => ['nullable', 'date'],
            'visible_until'    => ['nullable', 'date', 'after_or_equal:visible_from'],
            'is_active'        => ['sometimes', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
        ]);

        $perkSet->update([
            'name'             => $validated['name'],
            'description'      => $validated['description'] ?? null,
            'fiscal_ledger_id' => $validated['fiscal_ledger_id'] ?? null,
            'visible_from'     => $validated['visible_from'] ?? null,
            'visible_until'    => $validated['visible_until'] ?? null,
            'is_active'        => $request->boolean('is_active', true),
            'sort_order'       => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.perk-sets.index')
            ->with('success', ['message' => 'Perk set <span class="font-semibold">' . e($perkSet->name) . '</span> updated successfully.']);
    }

    public function destroy(VolunteerPerkSet $perkSet): RedirectResponse
    {
        $name = $perkSet->name;
        $perkSet->delete();

        return redirect()->route('admin.perk-sets.index')
            ->with('success', 'Perk set "' . $name . '" deleted. Any perks in this set are now unassigned.');
    }

    public function awards(VolunteerPerkSet $perkSet): View
    {
        $perkSet->load([
            'fiscalLedger',
            'perks' => fn ($q) => $q->with(['events', 'redemptions'])->orderBy('sort_order')->orderBy('min_hours'),
        ]);

        $report = $perkSet->perks->map(fn (VolunteerPerk $perk) => [
            'perk'    => $perk,
            'earners' => $this->resolveEarners($perk, $perkSet),
        ]);

        return view('admin.perk-sets.awards', compact('perkSet', 'report'));
    }

    private function resolveEarners(VolunteerPerk $perk, VolunteerPerkSet $perkSet): Collection
    {
        if ($perk->events->isNotEmpty()) {
            $eventIds = $perk->events->pluck('id');

            $shifts = Shift::with(['users' => fn ($q) => $q->where(
                fn ($q2) => $q2->whereNull('shift_signups.no_show')->orWhere('shift_signups.no_show', false)
            )])
                ->whereHas('event', fn ($q) => $q->whereIn('id', $eventIds))
                ->get();

            $userHours = [];
            foreach ($shifts as $shift) {
                $hours = $shift->durationInHours() * ($shift->double_hours ? 2 : 1);
                foreach ($shift->users as $shiftUser) {
                    $userHours[$shiftUser->id] = ($userHours[$shiftUser->id] ?? 0.0) + $hours;
                }
            }

            $earnerIds = array_keys(array_filter($userHours, fn ($h) => $h >= (float) $perk->min_hours));

            return User::whereIn('id', $earnerIds)->orderBy('name')->get();
        }

        // General hours-based perk — use VolunteerHours records
        $query = VolunteerHours::select('user_id')
            ->selectRaw('SUM(hours) as total_hours')
            ->groupBy('user_id')
            ->havingRaw('SUM(hours) >= ?', [(float) $perk->min_hours]);

        if ($perkSet->fiscal_ledger_id) {
            $query->where('fiscal_ledger_id', $perkSet->fiscal_ledger_id);
        }

        $earnerIds = $query->pluck('user_id');

        return User::whereIn('id', $earnerIds)->orderBy('name')->get();
    }
}

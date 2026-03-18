<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\VolunteerPerk;
use App\Models\VolunteerPerkRedemption;
use App\Models\VolunteerPerkSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerPerkController extends Controller
{
    public function index(): View
    {
        $perkSets = VolunteerPerkSet::with([
            'perks' => fn ($q) => $q->with('events')->orderBy('sort_order')->orderBy('min_hours'),
        ])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        $unassignedPerks = VolunteerPerk::with('events')
            ->whereNull('perk_set_id')
            ->orderBy('sort_order')
            ->orderBy('min_hours')
            ->get();

        return view('admin.perks.index', compact('perkSets', 'unassignedPerks'));
    }

    public function create(): View
    {
        $events   = Event::orderBy('start_date', 'desc')->get();
        $perkSets = VolunteerPerkSet::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.perks.create', compact('events', 'perkSets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'min_hours'           => ['required', 'numeric', 'min:0.01'],
            'perk_set_id'         => ['nullable', 'exists:volunteer_perk_sets,id'],
            'is_active'           => ['sometimes', 'boolean'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'event_ids'           => ['nullable', 'array'],
            'event_ids.*'         => ['exists:events,id'],
            'has_pass'            => ['sometimes', 'boolean'],
            'pass_label'          => ['nullable', 'string', 'max:255'],
            'has_physical_reward' => ['sometimes', 'boolean'],
            'reward_label'        => ['nullable', 'string', 'max:255'],
            'is_mystery'          => ['sometimes', 'boolean'],
        ]);

        $perk = VolunteerPerk::create([
            'name'                => $validated['name'],
            'description'         => $validated['description'] ?? null,
            'min_hours'           => $validated['min_hours'],
            'perk_set_id'         => $validated['perk_set_id'] ?? null,
            'is_active'           => $request->boolean('is_active', true),
            'sort_order'          => $validated['sort_order'] ?? 0,
            'has_pass'            => $request->boolean('has_pass', false),
            'pass_label'          => $validated['pass_label'] ?? null,
            'has_physical_reward' => $request->boolean('has_physical_reward', false),
            'reward_label'        => $validated['reward_label'] ?? null,
            'is_mystery'          => $request->boolean('is_mystery', false),
        ]);

        if (!empty($validated['event_ids'])) {
            $perk->events()->sync($validated['event_ids']);
        }

        return redirect()->route('admin.perks.index')
            ->with('success', ['message' => 'Perk <span class="text-brand-green font-semibold">' . e($perk->name) . '</span> created successfully.']);
    }

    public function edit(VolunteerPerk $perk): View
    {
        $perk->load(['perkSet', 'events']);
        $events   = Event::orderBy('start_date', 'desc')->get();
        $perkSets = VolunteerPerkSet::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.perks.create', compact('perk', 'events', 'perkSets'));
    }

    public function update(Request $request, VolunteerPerk $perk): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'min_hours'           => ['required', 'numeric', 'min:0.01'],
            'perk_set_id'         => ['nullable', 'exists:volunteer_perk_sets,id'],
            'is_active'           => ['sometimes', 'boolean'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'event_ids'           => ['nullable', 'array'],
            'event_ids.*'         => ['exists:events,id'],
            'has_pass'            => ['sometimes', 'boolean'],
            'pass_label'          => ['nullable', 'string', 'max:255'],
            'has_physical_reward' => ['sometimes', 'boolean'],
            'reward_label'        => ['nullable', 'string', 'max:255'],
            'is_mystery'          => ['sometimes', 'boolean'],
        ]);

        $perk->update([
            'name'                => $validated['name'],
            'description'         => $validated['description'] ?? null,
            'min_hours'           => $validated['min_hours'],
            'perk_set_id'         => $validated['perk_set_id'] ?? null,
            'is_active'           => $request->boolean('is_active', true),
            'sort_order'          => $validated['sort_order'] ?? 0,
            'has_pass'            => $request->boolean('has_pass', false),
            'pass_label'          => $validated['pass_label'] ?? null,
            'has_physical_reward' => $request->boolean('has_physical_reward', false),
            'reward_label'        => $validated['reward_label'] ?? null,
            'is_mystery'          => $request->boolean('is_mystery', false),
        ]);

        $perk->events()->sync($validated['event_ids'] ?? []);

        return redirect()->route('admin.perks.index')
            ->with('success', ['message' => 'Perk <span class="text-brand-green font-semibold">' . e($perk->name) . '</span> updated successfully.']);
    }

    public function destroy(VolunteerPerk $perk): RedirectResponse
    {
        $name = $perk->name;
        $perk->delete();

        return redirect()->route('admin.perks.index')
            ->with('success', 'Perk "' . $name . '" deleted.');
    }

    public function redemptions(VolunteerPerk $perk): View
    {
        $redemptions = $perk->redemptions()
            ->with('user')
            ->latest('redeemed_at')
            ->get();

        return view('admin.perks.redemptions', compact('perk', 'redemptions'));
    }

    public function resetRedemption(VolunteerPerk $perk, VolunteerPerkRedemption $redemption): RedirectResponse
    {
        abort_if($redemption->volunteer_perk_id !== $perk->id, 404);

        $userName = $redemption->user?->name ?? 'Unknown';
        $redemption->delete();

        return redirect()->route('admin.perks.redemptions', $perk)
            ->with('success', ['message' => 'Redemption for <span class="font-semibold">' . e($userName) . '</span> has been reset.']);
    }
}

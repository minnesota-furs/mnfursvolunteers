<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FiscalLedger;
use App\Models\VolunteerPerk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerPerkController extends Controller
{
    public function index(): View
    {
        $perks = VolunteerPerk::with(['fiscalLedger', 'events'])
            ->orderBy('sort_order')
            ->orderBy('min_hours')
            ->get();

        return view('admin.perks.index', compact('perks'));
    }

    public function create(): View
    {
        $events = Event::orderBy('start_date', 'desc')->get();
        $fiscalLedgers = FiscalLedger::orderBy('start_date', 'desc')->get();

        return view('admin.perks.create', compact('events', 'fiscalLedgers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'min_hours'        => ['required', 'numeric', 'min:0.01'],
            'fiscal_ledger_id' => ['nullable', 'exists:fiscal_ledgers,id'],
            'is_active'        => ['sometimes', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'event_ids'        => ['nullable', 'array'],
            'event_ids.*'      => ['exists:events,id'],
        ]);

        $perk = VolunteerPerk::create([
            'name'             => $validated['name'],
            'description'      => $validated['description'] ?? null,
            'min_hours'        => $validated['min_hours'],
            'fiscal_ledger_id' => $validated['fiscal_ledger_id'] ?? null,
            'is_active'        => $request->boolean('is_active', true),
            'sort_order'       => $validated['sort_order'] ?? 0,
        ]);

        if (!empty($validated['event_ids'])) {
            $perk->events()->sync($validated['event_ids']);
        }

        return redirect()->route('admin.perks.index')
            ->with('success', ['message' => 'Perk <span class="text-brand-green font-semibold">' . e($perk->name) . '</span> created successfully.']);
    }

    public function edit(VolunteerPerk $perk): View
    {
        $perk->load(['fiscalLedger', 'events']);
        $events = Event::orderBy('start_date', 'desc')->get();
        $fiscalLedgers = FiscalLedger::orderBy('start_date', 'desc')->get();

        return view('admin.perks.create', compact('perk', 'events', 'fiscalLedgers'));
    }

    public function update(Request $request, VolunteerPerk $perk): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'min_hours'        => ['required', 'numeric', 'min:0.01'],
            'fiscal_ledger_id' => ['nullable', 'exists:fiscal_ledgers,id'],
            'is_active'        => ['sometimes', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'event_ids'        => ['nullable', 'array'],
            'event_ids.*'      => ['exists:events,id'],
        ]);

        $perk->update([
            'name'             => $validated['name'],
            'description'      => $validated['description'] ?? null,
            'min_hours'        => $validated['min_hours'],
            'fiscal_ledger_id' => $validated['fiscal_ledger_id'] ?? null,
            'is_active'        => $request->boolean('is_active', true),
            'sort_order'       => $validated['sort_order'] ?? 0,
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
}

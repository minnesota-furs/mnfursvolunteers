<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::orderBy('start_date', 'asc')->get();
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'signup_open_date' => 'nullable|date|before_or_equal:end_date',
            'location'    => 'nullable|string',
            'visibility' => 'required|in:public,unlisted,draft',
            'hide_past_shifts' => 'nullable|boolean',
            'auto_credit_hours' => 'nullable|boolean',
        ]);

        // Normalize checkbox (unchecked checkboxes don't get sent)
        $validated['hide_past_shifts'] = $request->has('hide_past_shifts');
        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');

        Event::create([
            ...$request->only(['name', 'description', 'start_date', 'end_date', 'signup_open_date', 'location', 'visibility', 'hide_past_shifts', 'auto_credit_hours']),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', [
                'message' => "Event <span class=\"text-brand-green\">{$request->name}</span> created successfully",
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('admin.events.create', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'signup_open_date' => 'nullable|date|before_or_equal:end_date',
            'location'    => 'nullable|string',
            'visibility' => 'required|in:public,unlisted,draft',
            'hide_past_shifts' => 'nullable|boolean',
            'auto_credit_hours' => 'nullable|boolean',
        ]);

        // Normalize checkbox (unchecked checkboxes don't get sent)
        $validated['hide_past_shifts'] = $request->has('hide_past_shifts');
        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');

        $event->update($request->only(['name', 'description', 'start_date', 'end_date', 'signup_open_date', 'location', 'visibility', 'hide_past_shifts', 'auto_credit_hours']));

        return redirect()->route('admin.events.index')
            ->with('success', [
                'message' => "Event <span class=\"text-brand-green\">{$event->name}</span> updated successfully"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', [
            'message' => "Event <span class=\"text-brand-green\">{$event->name}</span> deleted"
        ]);
    }

    public function volunteerList(Event $event)
    {
        $volunteers = $event->shifts()
            ->with('users')
            ->get()
            ->pluck('users')
            ->flatten()
            ->unique('id')
            ->values();

        $bccList = $volunteers->map(fn($v) => "{$v->name}<{$v->email}>")->join(',');

        return view('admin.events.volunteers', compact('event', 'volunteers', 'bccList'));
    }

    public function agendaView(Event $event)
    {
        // Load all shifts and users signed up
        $shifts = $event->shifts()->with('users')->orderBy('start_time')->get();

        // Calculate visual position per shift to avoid overlap
        $positions = $this->assignShiftColumns($shifts);

        return view('admin.events.agenda', [
            'event' => $event,
            'shifts' => $shifts,
            'positions' => $positions,
            'startHour' => 5,
            'endHour' => 24,
        ]);
    }

    protected function assignShiftColumns($shifts)
    {
        $groups = [];
        foreach ($shifts as $shift) {
            $added = false;
            foreach ($groups as &$group) {
                $conflict = false;
                foreach ($group as $existing) {
                    $endBuffer = $existing->end_time->copy()->subMinute();
                    if (
                        $shift->start_time->lt($endBuffer) &&
                        $shift->end_time->gt($existing->start_time)
                    ) {
                        $conflict = true;
                        break;
                    }
                }
                if (!$conflict) {
                    $group[] = $shift;
                    $added = true;
                    break;
                }
            }
            if (!$added) {
                $groups[] = [$shift];
            }
        }

        $shiftPositions = [];
        foreach ($groups as $group) {
            foreach ($group as $i => $shift) {
                $shiftPositions[$shift->id] = [
                    'column' => $i,
                    'columns' => count($group),
                ];
            }
        }

        return $shiftPositions;
    }
}
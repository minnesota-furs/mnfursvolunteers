<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\AuditLog;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showPast = $request->boolean('show_past');
        $showMine = $request->boolean('show_mine');
        
        $query = Event::with(['creator', 'editors'])->orderBy('start_date', 'asc');
        
        if (!$showPast) {
            $query->upcoming();
        }
        
        if ($showMine) {
            $query->editableBy(auth()->id());
        }
        
        $events = $query->get();
        
        return view('admin.events.index', compact('events', 'showPast', 'showMine'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.events.create');
    }
    
    public function log(Event $event)
    {
        $this->authorize('update', $event);
        
        $logs = AuditLog::where('auditable_type', Event::class)
            ->where('auditable_id', $event->id)
            ->latest()
            ->paginate(20);
        return view('admin.events.log', compact('event', 'logs'));
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
        $this->authorize('update', $event);
        
        return view('admin.events.create', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
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
            'created_by' => 'nullable|exists:users,id',
        ]);

        // Build update data
        $updateData = $request->only(['name', 'description', 'start_date', 'end_date', 'signup_open_date', 'location', 'visibility']);
        
        // Normalize checkbox (unchecked checkboxes don't get sent)
        $updateData['hide_past_shifts'] = $request->has('hide_past_shifts');
        $updateData['auto_credit_hours'] = $request->has('auto_credit_hours');
        
        // Only admins with manage-events permission can change the creator
        if ($request->has('created_by') && auth()->user()->isAdmin()) {
            $updateData['created_by'] = $request->created_by;
        }
        
        $event->update($updateData);

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
        $this->authorize('delete', $event);
        
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', [
            'message' => "Event <span class=\"text-brand-green\">{$event->name}</span> deleted"
        ]);
    }

    public function volunteerList(Event $event)
    {
        $this->authorize('update', $event);
        
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

    public function indexWithShifts(Event $event)
        {
            $this->authorize('update', $event);
            
            $events = $event->shifts()
                ->with('users')
                ->orderBy('start_time')
                ->get();

            $shifts = $events->groupBy(function ($shift) {
                return $shift->start_time->format('Y-m-d');
            });

            return view('admin.shifts.allShifts', compact('event', 'shifts'));
        }

    public function indexWithShiftsPrint(Event $event)
        {
            $this->authorize('update', $event);
            
            $events = $event->shifts()
                ->with('users')
                ->orderBy('start_time')
                ->get();

            $shifts = $events->groupBy(function ($shift) {
                return $shift->start_time->format('Y-m-d');
            });

            return view('admin.shifts.allShiftsPrint', compact('event', 'shifts'));
        }

    public function agendaView(Event $event)
    {
        $this->authorize('update', $event);
        
        // Load all shifts and users signed up
        $shifts = $event->shifts()->with('users')->orderBy('start_time')->get();

        // Calculate visual position per shift to avoid overlap
        $positions = $this->assignShiftColumns($shifts);

        return view('admin.shifts.agenda', [
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

    /**
     * Show the event editors management page
     */
    public function editors(Event $event)
    {
        $this->authorize('manageEditors', $event);
        
        $editors = $event->editors()->get();
        $availableUsers = User::whereNotIn('id', $editors->pluck('id'))
            ->where('id', '!=', $event->created_by)
            ->orderBy('name')
            ->get();
        
        return view('admin.events.editors', compact('event', 'editors', 'availableUsers'));
    }

    /**
     * Add an editor to an event
     */
    public function addEditor(Request $request, Event $event)
    {
        $this->authorize('manageEditors', $event);
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        
        // Prevent adding the creator as an editor (they already have full access)
        if ($request->user_id == $event->created_by) {
            return redirect()->back()->with('error', [
                'message' => 'The event creator already has full edit permissions.'
            ]);
        }
        
        // Check if already an editor
        if ($event->editors()->where('user_id', $request->user_id)->exists()) {
            return redirect()->back()->with('error', [
                'message' => 'This user already has edit permissions for this event.'
            ]);
        }
        
        $event->editors()->attach($request->user_id);
        
        $user = User::find($request->user_id);
        return redirect()->back()->with('success', [
            'message' => "Added <span class=\"text-brand-green\">{$user->name}</span> as an editor for this event."
        ]);
    }

    /**
     * Remove an editor from an event
     */
    public function removeEditor(Event $event, User $user)
    {
        $this->authorize('manageEditors', $event);
        
        $event->editors()->detach($user->id);
        
        return redirect()->back()->with('success', [
            'message' => "Removed <span class=\"text-brand-green\">{$user->name}</span> as an editor for this event."
        ]);
    }
}
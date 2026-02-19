<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdvancedDuplicateEventRequest;
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
        $tags = \App\Models\Tag::orderBy('name')->get();
        return view('admin.events.create', compact('tags'));
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
            'visibility' => 'required|in:public,unlisted,internal,draft',
            'hide_past_shifts' => 'nullable|boolean',
            'auto_credit_hours' => 'nullable|boolean',
            'required_tags' => 'nullable|array',
            'required_tags.*' => 'exists:tags,id',
        ]);

        // Normalize checkbox (unchecked checkboxes don't get sent)
        $validated['hide_past_shifts'] = $request->has('hide_past_shifts');
        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');

        $event = Event::create([
            ...$request->only(['name', 'description', 'start_date', 'end_date', 'signup_open_date', 'location', 'visibility', 'hide_past_shifts', 'auto_credit_hours']),
            'created_by' => auth()->id(),
        ]);

        // Sync required tags
        $event->requiredTags()->sync($request->input('required_tags', []));

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
        
        $event->load('requiredTags');
        $tags = \App\Models\Tag::orderBy('name')->get();
        return view('admin.events.create', compact('event', 'tags'));
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
            'visibility' => 'required|in:public,unlisted,internal,draft',
            'hide_past_shifts' => 'nullable|boolean',
            'auto_credit_hours' => 'nullable|boolean',
            'created_by' => 'nullable|exists:users,id',
            'required_tags' => 'nullable|array',
            'required_tags.*' => 'exists:tags,id',
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

        // Sync required tags
        $event->requiredTags()->sync($request->input('required_tags', []));

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

    /**
     * Display the agenda/calendar view for an event
     */
    public function agenda(Event $event)
    {
        $this->authorize('update', $event);
        
        $shifts = $event->shifts()
            ->with('users')
            ->orderBy('start_time')
            ->get();

        // Group shifts by date
        $shiftsByDate = $shifts->groupBy(function ($shift) {
            return $shift->start_time->format('Y-m-d');
        });

        // Calculate time range for the calendar (earliest to latest)
        $earliestHour = 24;
        $latestHour = 0;
        
        foreach ($shifts as $shift) {
            $startHour = (int) $shift->start_time->format('G');
            $endHour = (int) $shift->end_time->format('G');
            
            if ($shift->end_time->format('i') > 0) {
                $endHour++; // Round up if there are minutes
            }
            
            $earliestHour = min($earliestHour, $startHour);
            $latestHour = max($latestHour, $endHour);
        }

        // Default to reasonable hours if no shifts
        if ($shifts->isEmpty()) {
            $earliestHour = 8;
            $latestHour = 18;
        }

        // Ensure we have at least a reasonable window
        $earliestHour = max(0, $earliestHour - 1);
        $latestHour = min(24, $latestHour + 1);

        // Calculate statistics
        $totalSlots = $shifts->sum('max_volunteers');
        $filledSlots = $shifts->sum(function ($shift) {
            return $shift->users->count();
        });
        $coveragePercent = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100) : 0;

        // Calculate column positions for overlapping shifts per day
        $shiftPositions = [];
        foreach ($shiftsByDate as $date => $dayShifts) {
            $positions = $this->assignShiftColumns($dayShifts);
            foreach ($positions as $shiftId => $position) {
                $shiftPositions[$shiftId] = $position;
            }
        }

        return view('admin.shifts.agenda', compact(
            'event', 
            'shifts', 
            'shiftsByDate', 
            'earliestHour', 
            'latestHour',
            'totalSlots',
            'filledSlots',
            'coveragePercent',
            'shiftPositions'
        ));
    }

    protected function assignShiftColumns($shifts)
    {
        // Sort shifts by start time
        $sortedShifts = $shifts->sortBy('start_time')->values();
        
        $columns = [];
        $shiftPositions = [];
        
        foreach ($sortedShifts as $shift) {
            $placed = false;
            
            // Try to place shift in an existing column
            foreach ($columns as $columnIndex => $columnShifts) {
                $hasConflict = false;
                
                foreach ($columnShifts as $existingShift) {
                    // Check if shifts overlap (with 1-minute buffer to allow back-to-back)
                    if ($shift->start_time->lt($existingShift->end_time->copy()->subMinute()) &&
                        $shift->end_time->gt($existingShift->start_time->copy()->addMinute())) {
                        $hasConflict = true;
                        break;
                    }
                }
                
                if (!$hasConflict) {
                    // Place in this column
                    $columns[$columnIndex][] = $shift;
                    $placed = true;
                    break;
                }
            }
            
            if (!$placed) {
                // Create new column
                $columns[] = [$shift];
            }
        }
        
        // Calculate total columns needed (max columns at any time)
        $maxColumns = count($columns);
        
        // Assign positions to each shift
        foreach ($columns as $columnIndex => $columnShifts) {
            foreach ($columnShifts as $shift) {
                $shiftPositions[$shift->id] = [
                    'column' => $columnIndex,
                    'columns' => $maxColumns,
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

    /**
     * Show the advanced duplicate modal for an event
     */
    public function showDuplicateModal(Event $event)
    {
        $this->authorize('update', $event);
        return response()->json([
            'event' => $event,
            'shifts_count' => $event->shifts()->count(),
        ]);
    }

    /**
     * Process advanced duplicate request to create event copy with all shifts (no volunteers)
     */
    public function advancedDuplicate(AdvancedDuplicateEventRequest $request, Event $event)
    {
        $this->authorize('update', $event);
        
        $validated = $request->validated();

        try {
            // Create new event
            $newEvent = $event->replicate();
            $newEvent->name = $validated['event_name'];
            $newEvent->created_by = auth()->id();

            // Handle event date adjustment
            $eventDateOffset = null;
            if ($validated['adjust_event_dates'] ?? false) {
                $offsetValue = $validated['event_date_offset_value'] ?? 1;
                $offsetUnit = $validated['event_date_offset_unit'] ?? 'days';
                
                $eventDateOffset = [
                    'value' => $offsetValue,
                    'unit' => $offsetUnit,
                ];

                $newStartDate = clone $event->start_date;
                $newEndDate = clone $event->end_date;

                match ($offsetUnit) {
                    'days' => [
                        $newStartDate->addDays($offsetValue),
                        $newEndDate->addDays($offsetValue),
                    ],
                    'weeks' => [
                        $newStartDate->addWeeks($offsetValue),
                        $newEndDate->addWeeks($offsetValue),
                    ],
                    'months' => [
                        $newStartDate->addMonths($offsetValue),
                        $newEndDate->addMonths($offsetValue),
                    ],
                    'years' => [
                        $newStartDate->addYears($offsetValue),
                        $newEndDate->addYears($offsetValue),
                    ],
                };

                $newEvent->start_date = $newStartDate;
                $newEvent->end_date = $newEndDate;
            }

            $newEvent->save();

            // Copy required tags if requested
            if ($validated['copy_required_tags'] ?? false) {
                $tagIds = $event->requiredTags()->pluck('tags.id')->toArray();
                $newEvent->requiredTags()->sync($tagIds);
            }

            // Duplicate shifts without volunteers
            $shiftMapping = []; // Map old shift IDs to new shift IDs

            foreach ($event->shifts as $shift) {
                $newShift = $shift->replicate();
                $newShift->event_id = $newEvent->id;

                // Adjust shift dates if event dates are being adjusted
                if ($eventDateOffset) {
                    $newStartTime = clone $shift->start_time;
                    $newEndTime = clone $shift->end_time;

                    match ($eventDateOffset['unit']) {
                        'days' => [
                            $newStartTime->addDays($eventDateOffset['value']),
                            $newEndTime->addDays($eventDateOffset['value']),
                        ],
                        'weeks' => [
                            $newStartTime->addWeeks($eventDateOffset['value']),
                            $newEndTime->addWeeks($eventDateOffset['value']),
                        ],
                        'months' => [
                            $newStartTime->addMonths($eventDateOffset['value']),
                            $newEndTime->addMonths($eventDateOffset['value']),
                        ],
                        'years' => [
                            $newStartTime->addYears($eventDateOffset['value']),
                            $newEndTime->addYears($eventDateOffset['value']),
                        ],
                    };

                    $newShift->start_time = $newStartTime;
                    $newShift->end_time = $newEndTime;
                }

                $newShift->original_shift_id = $shift->id;
                $newShift->save();

                $shiftMapping[$shift->id] = $newShift->id;
            }

            // Log the action
            AuditLog::create([
                'action'         => 'event_advanced_duplicate',
                'auditable_type' => Event::class,
                'auditable_id'   => $newEvent->id,
                'comment'        => "User " . auth()->user()->name . " duplicated event '{$event->name}' (ID: {$event->id}) as '{$newEvent->name}' with " . count($shiftMapping) . " shifts (no volunteers copied)",
                'user_id'        => auth()->id(),
            ]);

            return redirect()->route('admin.events.index')
                ->with('success', [
                    'message' => "Event <span class=\"text-brand-green\">{$validated['event_name']}</span> created with " . count($shiftMapping) . " shifts",
                ]);
        } catch (\Exception $e) {
            return back()->with('error', [
                'message' => 'Failed to duplicate event: ' . $e->getMessage(),
            ]);
        }
    }
}
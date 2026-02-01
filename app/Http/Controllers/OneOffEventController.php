<?php

namespace App\Http\Controllers;

use App\Models\OneOffEvent;
use App\Models\OneOffEventCheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OneOffEventController extends Controller
{
    // Show list of upcoming events
    public function index()
    {
        $events = OneOffEvent::where('end_time', '>=', now())->orderBy('start_time')->get();
        return view('one_off_events.index', compact('events'));
    }

    // Show list of archived/past events (admin only)
    public function archived()
    {
        $events = OneOffEvent::where('end_time', '<', now())
            ->orderBy('end_time', 'desc')
            ->paginate(20);
        return view('one_off_events.archived', compact('events'));
    }

    // Show a single event
    public function show(OneOffEvent $oneOffEvent)
    {
        $checkIn = null;
        if (Auth::check()) {
            $checkIn = OneOffEventCheckIn::where('user_id', Auth::id())
                ->where('one_off_event_id', $oneOffEvent->id)
                ->first();
        }

        return view('one_off_events.show', compact('oneOffEvent', 'checkIn'));
    }

    // Show form to create an event (admin)
    public function create()
    {
        return view('one_off_events.create');
    }

    // Store event (admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'checkin_hours_before' => 'nullable|integer|min:0|max:48',
            'checkin_hours_after' => 'nullable|integer|min:0|max:72',
        ]);

        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');
        $validated['checkin_hours_before'] = $validated['checkin_hours_before'] ?? 1;
        $validated['checkin_hours_after'] = $validated['checkin_hours_after'] ?? 12;

        OneOffEvent::create($validated);

        return redirect()->route('one-off-events.index')->with('success', [
                'message' => "One Off Event <span class=\"text-brand-green\">Created Successfully</span>"
            ]);
    }

    // Edit event (admin)
    public function edit(OneOffEvent $oneOffEvent)
    {
        return view('one_off_events.edit', compact('oneOffEvent'));
    }

    // Update event (admin)
    public function update(Request $request, OneOffEvent $oneOffEvent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'checkin_hours_before' => 'nullable|integer|min:0|max:48',
            'checkin_hours_after' => 'nullable|integer|min:0|max:72',
        ]);

        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');
        $validated['checkin_hours_before'] = $validated['checkin_hours_before'] ?? 1;
        $validated['checkin_hours_after'] = $validated['checkin_hours_after'] ?? 12;

        $oneOffEvent->update($validated);

        return redirect()->route('one-off-events.show', $oneOffEvent)->with('success', [
                'message' => "Event <span class=\"text-brand-green\">Updated Successfully</span>"
            ]);
    }

    // Delete event (admin)
    public function destroy(OneOffEvent $oneOffEvent)
    {
        $oneOffEvent->delete();

        return redirect()->route('one-off-events.index')->with('success', [
                'message' => "Event <span class=\"text-brand-green\">Deleted Successfully</span>"
            ]);
    }

    // Duplicate event (admin)
    public function duplicate(OneOffEvent $oneOffEvent)
    {
        $newEvent = $oneOffEvent->replicate();
        $newEvent->name = $oneOffEvent->name . ' (Copy)';
        $newEvent->save();

        return redirect()->route('one-off-events.edit', $newEvent)->with('success', [
            'message' => "Event <span class=\"text-brand-green\">Duplicated Successfully</span> - Update the details below"
        ]);
    }

    // View all check-ins for an event (admin)
    public function checkIns(OneOffEvent $oneOffEvent)
    {
        $checkIns = $oneOffEvent->checkIns()
            ->with('user')
            ->orderBy('checked_in_at', 'desc')
            ->get();

        return view('one_off_events.check_ins', compact('oneOffEvent', 'checkIns'));
    }

    // Manually credit hours for a check-in (admin)
    public function manualCreditHours(OneOffEvent $oneOffEvent, OneOffEventCheckIn $checkIn)
    {
        if ($checkIn->hours_credited) {
            return back()->with('error', [
                'message' => 'Hours have already been credited for this check-in.'
            ]);
        }

        $volunteerHours = $checkIn->creditHours();

        if ($volunteerHours) {
            return back()->with('success', [
                'message' => "<span class=\"text-brand-green\">{$volunteerHours->hours} hours</span> have been credited to {$checkIn->user->name}"
            ]);
        }

        return back()->with('error', [
            'message' => 'Failed to credit hours.'
        ]);
    }

        // Check in to an event
    public function checkIn(OneOffEvent $oneOffEvent)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', [
                'message' => 'Please log in to check in.'
            ]);
        }

        // Check if already checked in
        $existingCheckIn = OneOffEventCheckIn::where('one_off_event_id', $oneOffEvent->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingCheckIn) {
            return back()->with('error', [
                'message' => 'You have already checked in to this event.'
            ]);
        }

        // Only allow check-in if now is within the event timeframe
        $now = now();
        $hoursBeforeStart = $oneOffEvent->checkin_hours_before ?? 1;
        $hoursAfterEnd = $oneOffEvent->checkin_hours_after ?? 12;
        
        $checkInStart = $oneOffEvent->start_time->copy()->subHours($hoursBeforeStart);
        $checkInEnd = $oneOffEvent->end_time->copy()->addHours($hoursAfterEnd);
        
        if ($now->isBefore($checkInStart) || $now->isAfter($checkInEnd)) {
            return back()->with('error', [
                'message' => "Check-in is only available {$hoursBeforeStart} hour(s) before the event starts until {$hoursAfterEnd} hour(s) after it ends."
            ]);
        }

        // Create check-in record
        $checkIn = OneOffEventCheckIn::create([
            'one_off_event_id' => $oneOffEvent->id,
            'user_id' => Auth::id(),
            'checked_in_at' => $now,
            'hours_credited' => false,
        ]);

        // Automatically credit hours if enabled
        if ($oneOffEvent->auto_credit_hours) {
            $volunteerHours = $checkIn->creditHours();
            
            if ($volunteerHours) {
                return back()->with('success', [
                    'message' => "You've been checked in and <span class=\"text-brand-green\">{$volunteerHours->hours} hours</span> have been credited to your account!"
                ]);
            }
        }

        return back()->with('success', [
            'message' => "You've been checked in successfully!"
        ]);
    }
}

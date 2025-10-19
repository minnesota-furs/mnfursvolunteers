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
        ]);

        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');

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
        ]);

        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');

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
        $checkInStart = $oneOffEvent->start_time->copy()->subHours(1);
        $checkInEnd = $oneOffEvent->end_time->copy()->addHours(12);
        
        if ($now->lt($checkInStart) || $now->gt($checkInEnd)) {
            return back()->with('error', [
                'message' => 'Check-in is only available 1 hour before the event starts until 12 hours after it ends.'
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

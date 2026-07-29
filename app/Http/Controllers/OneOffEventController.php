<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\OneOffEvent;
use App\Models\OneOffEventCheckIn;
use App\Models\OneOffEventRsvp;
use App\Models\Sector;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OneOffEventController extends Controller
{
    // Show list of upcoming events
    public function index()
    {
        $events = OneOffEvent::where(function ($query) {
                $query->where('end_time', '>=', now())->orWhereNull('end_time');
            })
            ->orderBy('start_time')
            ->get();

        $pastEvents = OneOffEvent::where('end_time', '<', now())
            ->orderByDesc('start_time')
            ->paginate(5, ['*'], 'past_page');

        $checkedInEventIds = OneOffEventCheckIn::where('user_id', Auth::id())
            ->pluck('one_off_event_id')
            ->all();

        $rsvpedEventIds = OneOffEventRsvp::where('user_id', Auth::id())
            ->pluck('one_off_event_id')
            ->all();

        return view('one_off_events.index', compact('events', 'pastEvents', 'checkedInEventIds', 'rsvpedEventIds'));
    }

    // Standby QR scanner for staff to look up volunteers and check them in on the spot (admin)
    public function scanner()
    {
        return view('one_off_events.scanner');
    }

    // Look up a volunteer by their vol_code and list the Simple Events they're RSVP'd for (admin)
    public function scannerLookup(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $code = strtoupper(trim($validated['code']));

        $user = User::where('vol_code', $code)->first();

        if (!$user) {
            return response()->json([
                'found' => false,
                'message' => "No volunteer found for code \"{$code}\".",
            ], 404);
        }

        $rsvpedEvents = OneOffEvent::whereHas('rsvps', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where(function ($query) {
                $query->whereNull('end_time')->orWhere('end_time', '>=', now()->subDay());
            })
            ->orderBy('start_time')
            ->get();

        $checkedInEventIds = OneOffEventCheckIn::where('user_id', $user->id)
            ->whereIn('one_off_event_id', $rsvpedEvents->pluck('id'))
            ->pluck('one_off_event_id')
            ->all();

        return response()->json([
            'found' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->displayName(),
                'pronouns' => $user->pronouns,
                'vol_code' => $user->vol_code,
                'department' => $user->department?->name,
                'profile_url' => route('users.show', $user),
            ],
            'events' => $rsvpedEvents->map(function ($event) use ($checkedInEventIds) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'location' => $event->location,
                    'start_time_human' => $event->start_time->format('D, M j \a\t g:i A'),
                    'is_happening_now' => $event->isHappeningNow(),
                    'has_ended' => $event->hasEnded(),
                    'checked_in' => in_array($event->id, $checkedInEventIds),
                    'check_in_url' => route('simple-volunteer-events.staff-check-in', $event),
                    'show_url' => route('simple-volunteer-events.show', $event),
                ];
            })->values(),
        ]);
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
        $oneOffEvent->load('requiredUserTags', 'requiredDepartments', 'requiredSectors');

        $checkIn = null;
        $rsvp = null;
        $isEligible = true;
        $missingTagNames = [];
        $eligibleDepartmentNames = [];
        $eligibleSectorNames = [];

        if (Auth::check()) {
            $user = Auth::user();

            if ($oneOffEvent->isCheckInType()) {
                $checkIn = OneOffEventCheckIn::where('user_id', $user->id)
                    ->where('one_off_event_id', $oneOffEvent->id)
                    ->first();
            } else {
                $rsvp = OneOffEventRsvp::where('user_id', $user->id)
                    ->where('one_off_event_id', $oneOffEvent->id)
                    ->first();
            }

            [$isEligible, $missingTagNames, $eligibleDepartmentNames, $eligibleSectorNames] = $this->checkEligibility($oneOffEvent, $user);
        }

        return view('one_off_events.show', compact('oneOffEvent', 'checkIn', 'rsvp', 'isEligible', 'missingTagNames', 'eligibleDepartmentNames', 'eligibleSectorNames'));
    }

    // Show form to create an event (admin)
    public function create()
    {
        $tags = Tag::forUsers()->orderBy('name')->get();
        $sectors = Sector::with(['departments' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();

        return view('one_off_events.create', compact('tags', 'sectors'));
    }

    // Store event (admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:2048',
            'type' => 'required|in:check_in,rsvp',
            'max_rsvps' => 'nullable|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'checkin_hours_before' => 'nullable|integer|min:0|max:48',
            'checkin_hours_after' => 'nullable|integer|min:0|max:72',
            'required_tags' => 'nullable|array',
            'required_tags.*' => 'exists:tags,id',
            'required_departments' => 'nullable|array',
            'required_departments.*' => 'exists:departments,id',
            'required_sectors' => 'nullable|array',
            'required_sectors.*' => 'exists:sectors,id',
        ]);

        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');
        $validated['checkin_hours_before'] = $validated['checkin_hours_before'] ?? 1;
        $validated['checkin_hours_after'] = $validated['checkin_hours_after'] ?? 12;

        if (empty($validated['end_time']) && $validated['auto_credit_hours']) {
            return back()->withErrors([
                'auto_credit_hours' => 'Automatic hour crediting requires an end time so hours can be calculated.'
            ])->withInput();
        }

        $event = OneOffEvent::create($validated);

        $event->requiredTags()->sync($request->input('required_tags', []));
        $event->requiredDepartments()->sync($request->input('required_departments', []));
        $event->requiredSectors()->sync($request->input('required_sectors', []));

        return redirect()->route('simple-volunteer-events.index')->with('success', [
                'message' => "Simple Volunteer Event <span class=\"text-brand-green\">Created Successfully</span>"
            ]);
    }

    // Edit event (admin)
    public function edit(OneOffEvent $oneOffEvent)
    {
        $oneOffEvent->load('requiredTags', 'requiredDepartments', 'requiredSectors');
        $tags = Tag::forUsers()->orderBy('name')->get();
        $sectors = Sector::with(['departments' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();

        return view('one_off_events.edit', compact('oneOffEvent', 'tags', 'sectors'));
    }

    // Update event (admin)
    public function update(Request $request, OneOffEvent $oneOffEvent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:2048',
            'type' => 'required|in:check_in,rsvp',
            'max_rsvps' => 'nullable|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'checkin_hours_before' => 'nullable|integer|min:0|max:48',
            'checkin_hours_after' => 'nullable|integer|min:0|max:72',
            'required_tags' => 'nullable|array',
            'required_tags.*' => 'exists:tags,id',
            'required_departments' => 'nullable|array',
            'required_departments.*' => 'exists:departments,id',
            'required_sectors' => 'nullable|array',
            'required_sectors.*' => 'exists:sectors,id',
        ]);

        $validated['auto_credit_hours'] = $request->has('auto_credit_hours');
        $validated['checkin_hours_before'] = $validated['checkin_hours_before'] ?? 1;
        $validated['checkin_hours_after'] = $validated['checkin_hours_after'] ?? 12;

        if (empty($validated['end_time']) && $validated['auto_credit_hours']) {
            return back()->withErrors([
                'auto_credit_hours' => 'Automatic hour crediting requires an end time so hours can be calculated.'
            ])->withInput();
        }

        $oneOffEvent->update($validated);

        $oneOffEvent->requiredTags()->sync($request->input('required_tags', []));
        $oneOffEvent->requiredDepartments()->sync($request->input('required_departments', []));
        $oneOffEvent->requiredSectors()->sync($request->input('required_sectors', []));

        return redirect()->route('simple-volunteer-events.show', $oneOffEvent)->with('success', [
                'message' => "Event <span class=\"text-brand-green\">Updated Successfully</span>"
            ]);
    }

    // Delete event (admin)
    public function destroy(OneOffEvent $oneOffEvent)
    {
        $oneOffEvent->delete();

        return redirect()->route('simple-volunteer-events.index')->with('success', [
                'message' => "Event <span class=\"text-brand-green\">Deleted Successfully</span>"
            ]);
    }

    // Duplicate event (admin)
    public function duplicate(OneOffEvent $oneOffEvent)
    {
        $newEvent = $oneOffEvent->replicate();
        $newEvent->name = $oneOffEvent->name . ' (Copy)';
        $newEvent->save();

        $newEvent->requiredTags()->sync($oneOffEvent->requiredTags->pluck('id'));
        $newEvent->requiredDepartments()->sync($oneOffEvent->requiredDepartments->pluck('id'));
        $newEvent->requiredSectors()->sync($oneOffEvent->requiredSectors->pluck('id'));

        return redirect()->route('simple-volunteer-events.edit', $newEvent)->with('success', [
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

    // Credit hours for every pending check-in on an event (admin)
    public function creditAllHours(OneOffEvent $oneOffEvent)
    {
        $pendingCheckIns = $oneOffEvent->checkIns()->where('hours_credited', false)->get();

        $creditedCount = 0;
        foreach ($pendingCheckIns as $checkIn) {
            if ($checkIn->creditHours()) {
                $creditedCount++;
            }
        }

        if ($creditedCount === 0) {
            return back()->with('error', [
                'message' => 'No pending check-ins to credit.'
            ]);
        }

        return back()->with('success', [
            'message' => "Hours credited to <span class=\"text-brand-green\">{$creditedCount}</span> volunteer" . ($creditedCount !== 1 ? 's' : '')
        ]);
    }

    // Remove a check-in (admin)
    public function destroyCheckIn(OneOffEvent $oneOffEvent, OneOffEventCheckIn $checkIn)
    {
        $userName = $checkIn->user->name;
        $checkIn->delete();

        return back()->with('success', [
            'message' => "Check-in for <span class=\"text-brand-green\">{$userName}</span> removed"
        ]);
    }

    // View all RSVPs for an event (admin)
    public function rsvps(OneOffEvent $oneOffEvent)
    {
        $rsvps = $oneOffEvent->rsvps()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('one_off_events.rsvps', compact('oneOffEvent', 'rsvps'));
    }

    // Remove a user's RSVP (admin)
    public function destroyRsvp(OneOffEvent $oneOffEvent, OneOffEventRsvp $rsvp)
    {
        $rsvp->delete();

        return back()->with('success', [
            'message' => "RSVP for <span class=\"text-brand-green\">{$rsvp->user->name}</span> removed"
        ]);
    }

    // RSVP to an event
    public function rsvp(Request $request, OneOffEvent $oneOffEvent)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', [
                'message' => 'Please log in to RSVP.'
            ]);
        }

        if (!$oneOffEvent->isRsvpType()) {
            abort(404);
        }

        if (OneOffEventRsvp::where('one_off_event_id', $oneOffEvent->id)->where('user_id', Auth::id())->exists()) {
            return back()->with('error', [
                'message' => 'You have already RSVP\'d to this event.'
            ]);
        }

        if ($oneOffEvent->hasEnded()) {
            return back()->with('error', [
                'message' => 'This event has already ended.'
            ]);
        }

        if ($oneOffEvent->isRsvpFull()) {
            return back()->with('error', [
                'message' => 'This event has reached its maximum number of RSVPs.'
            ]);
        }

        [$isEligible, $missingTagNames, $eligibleDepartmentNames, $eligibleSectorNames] = $this->checkEligibility($oneOffEvent, Auth::user());

        if (!$isEligible) {
            if (!empty($missingTagNames)) {
                return back()->with('error', [
                    'message' => 'You must have the following tag(s) to RSVP to this event: ' . implode(', ', $missingTagNames)
                ]);
            }

            $groups = $eligibleDepartmentNames;
            foreach ($eligibleSectorNames as $sectorName) {
                $groups[] = "any department in the {$sectorName} sector";
            }

            return back()->with('error', [
                'message' => 'You must be assigned to one of the following to RSVP to this event: ' . implode(', ', $groups)
            ]);
        }

        $canTelegram = Auth::user()->hasTelegramLinked();

        OneOffEventRsvp::create([
            'one_off_event_id' => $oneOffEvent->id,
            'user_id' => Auth::id(),
            'remind_morning_of_email' => $request->boolean('remind_morning_of_email'),
            'remind_morning_of_telegram' => $canTelegram && $request->boolean('remind_morning_of_telegram'),
            'remind_hour_before_email' => $request->boolean('remind_hour_before_email'),
            'remind_hour_before_telegram' => $canTelegram && $request->boolean('remind_hour_before_telegram'),
        ]);

        return back()->with('success', [
            'message' => "You're RSVP'd! We'll see you there."
        ]);
    }

    // Cancel a user's own RSVP
    public function cancelRsvp(OneOffEvent $oneOffEvent)
    {
        OneOffEventRsvp::where('one_off_event_id', $oneOffEvent->id)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', [
            'message' => 'Your RSVP has been cancelled.'
        ]);
    }

    // Update a user's own reminder preferences for their RSVP
    public function updateRsvpReminders(Request $request, OneOffEvent $oneOffEvent)
    {
        $rsvp = OneOffEventRsvp::where('one_off_event_id', $oneOffEvent->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $canTelegram = Auth::user()->hasTelegramLinked();

        $rsvp->update([
            'remind_morning_of_email' => $request->boolean('remind_morning_of_email'),
            'remind_morning_of_telegram' => $canTelegram && $request->boolean('remind_morning_of_telegram'),
            'remind_hour_before_email' => $request->boolean('remind_hour_before_email'),
            'remind_hour_before_telegram' => $canTelegram && $request->boolean('remind_hour_before_telegram'),
        ]);

        return back()->with('success', [
            'message' => 'Your reminder preferences have been updated.'
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

        if (!$oneOffEvent->isCheckInType()) {
            abort(404);
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

        // Check eligibility restrictions (required tags / departments / sectors)
        [$isEligible, $missingTagNames, $eligibleDepartmentNames, $eligibleSectorNames] = $this->checkEligibility($oneOffEvent, Auth::user());

        if (!$isEligible) {
            if (!empty($missingTagNames)) {
                return back()->with('error', [
                    'message' => 'You must have the following tag(s) to check in to this event: ' . implode(', ', $missingTagNames)
                ]);
            }

            $groups = $eligibleDepartmentNames;
            foreach ($eligibleSectorNames as $sectorName) {
                $groups[] = "any department in the {$sectorName} sector";
            }

            return back()->with('error', [
                'message' => 'You must be assigned to one of the following to check in to this event: ' . implode(', ', $groups)
            ]);
        }

        // Only allow check-in if now is within the event timeframe.
        // Events with no end time are open-ended, so there's no closing bound.
        $now = now();
        $hoursBeforeStart = $oneOffEvent->checkin_hours_before ?? 1;
        $hoursAfterEnd = $oneOffEvent->checkin_hours_after ?? 12;

        $checkInStart = $oneOffEvent->start_time->copy()->subHours($hoursBeforeStart);
        $checkInEnd = $oneOffEvent->end_time
            ? $oneOffEvent->end_time->copy()->addHours($hoursAfterEnd)
            : null;

        if ($now->isBefore($checkInStart) || ($checkInEnd && $now->isAfter($checkInEnd))) {
            return back()->with('error', [
                'message' => $checkInEnd
                    ? "Check-in is only available {$hoursBeforeStart} hour(s) before the event starts until {$hoursAfterEnd} hour(s) after it ends."
                    : "Check-in is only available starting {$hoursBeforeStart} hour(s) before the event starts."
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

    // Staff-assisted check-in for another volunteer, e.g. from the QR scanner (admin)
    public function staffCheckIn(Request $request, OneOffEvent $oneOffEvent)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $existingCheckIn = OneOffEventCheckIn::where('one_off_event_id', $oneOffEvent->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingCheckIn) {
            return response()->json([
                'ok' => false,
                'message' => "{$user->displayName()} is already checked in to this event.",
            ], 422);
        }

        OneOffEventCheckIn::create([
            'one_off_event_id' => $oneOffEvent->id,
            'user_id' => $user->id,
            'checked_in_at' => now(),
            'hours_credited' => false,
        ]);

        return response()->json([
            'ok' => true,
            'message' => "{$user->displayName()} has been checked in.",
        ]);
    }

    /**
     * Determine whether a user meets an event's tag/department/sector restrictions.
     * Mirrors ShiftSignupController's eligibility logic: all required tags
     * must be present, and the user must belong to at least one required
     * department OR to any department under a required sector.
     *
     * @return array{0: bool, 1: array<string>, 2: array<string>, 3: array<string>} [isEligible, missingTagNames, eligibleDepartmentNames, eligibleSectorNames]
     */
    private function checkEligibility(OneOffEvent $oneOffEvent, $user): array
    {
        $oneOffEvent->loadMissing('requiredUserTags', 'requiredDepartments', 'requiredSectors');

        $missingTagNames = [];
        if ($oneOffEvent->requiredUserTags->isNotEmpty()) {
            $userTagIds = $user->tags()->pluck('tags.id')->toArray();
            $requiredTagIds = $oneOffEvent->requiredUserTags->pluck('id')->toArray();

            $missingTagIds = array_diff($requiredTagIds, $userTagIds);
            if (!empty($missingTagIds)) {
                $missingTagNames = $oneOffEvent->requiredUserTags->whereIn('id', $missingTagIds)->pluck('name')->toArray();
            }
        }

        $eligibleDepartmentNames = [];
        $eligibleSectorNames = [];
        $meetsDepartmentRequirement = true;

        $hasDepartmentRestriction = $oneOffEvent->requiredDepartments->isNotEmpty();
        $hasSectorRestriction = $oneOffEvent->requiredSectors->isNotEmpty();

        if ($hasDepartmentRestriction || $hasSectorRestriction) {
            $userDeptIds = $user->departments()->pluck('departments.id')->toArray();
            $requiredDeptIds = $oneOffEvent->requiredDepartments->pluck('id')->toArray();

            // Anyone in a department under a required sector also qualifies,
            // so expand the eligible pool to include those department ids.
            $sectorDeptIds = $hasSectorRestriction
                ? Department::whereIn('sector_id', $oneOffEvent->requiredSectors->pluck('id'))->pluck('id')->toArray()
                : [];

            $eligibleDeptIdPool = array_unique(array_merge($requiredDeptIds, $sectorDeptIds));

            $meetsDepartmentRequirement = !empty(array_intersect($eligibleDeptIdPool, $userDeptIds));
            if (!$meetsDepartmentRequirement) {
                $eligibleDepartmentNames = $oneOffEvent->requiredDepartments->pluck('name')->toArray();
                $eligibleSectorNames = $oneOffEvent->requiredSectors->pluck('name')->toArray();
            }
        }

        $isEligible = empty($missingTagNames) && $meetsDepartmentRequirement;

        return [$isEligible, $missingTagNames, $eligibleDepartmentNames, $eligibleSectorNames];
    }
}

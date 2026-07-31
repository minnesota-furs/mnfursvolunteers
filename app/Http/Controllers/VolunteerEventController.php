<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Shift;
use Illuminate\Support\Carbon;

class VolunteerEventController extends Controller
{
    public function index()
    {
        $events = Event::visibleToAuthUsers()
            ->where('start_date', '>=', Carbon::now())
            ->orderBy('start_date')
            ->with(['requiredTags', 'requiredUserTags', 'requiredDepartments', 'requiredSectors', 'shifts.users'])
            ->withCount(['perks as active_perks_count' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $pastEvents = Event::visibleToAuthUsers()
            ->where('start_date', '<', Carbon::now())
            ->orderByDesc('start_date')
            ->with(['requiredTags', 'requiredUserTags', 'requiredDepartments', 'requiredSectors', 'shifts.users'])
            ->withCount(['perks as active_perks_count' => fn ($q) => $q->where('is_active', true)])
            ->paginate(5, ['*'], 'past_page');

        $user = auth()->user();
        $userTagIds = $user->tags()->pluck('tags.id')->all();
        $userDeptIds = $user->departments()->pluck('departments.id')->all();
        $userSectorIds = $user->departments()->pluck('sector_id')->unique()->all();
        $workedEventIds = $user->shifts()->pluck('event_id')->unique()->all();

        return view('events.index', compact('events', 'pastEvents', 'userTagIds', 'userDeptIds', 'userSectorIds', 'workedEventIds'));
    }

    public function show(Event $event)
    {
        // Load users for use in shift->users and required tags/departments
        $event->load('shifts.users', 'requiredTags', 'requiredUserTags', 'requiredDepartments', 'requiredSectors', 'perks');
        $user = auth()->user();
        $hasSignedUpForEvent = $user->shifts()->where('event_id', $event->id)->exists();

        // Block ineligible users from viewing shifts if the event requires eligibility.
        // Keep existing signups accessible even if eligibility requirements changed later.
        if ($event->require_eligibility && ! $hasSignedUpForEvent) {
            $userTagIds = $user->tags()->pluck('tags.id')->toArray();
            $requiredTagIds = $event->requiredUserTags->pluck('id')->toArray();
            $hasAllTags = empty(array_diff($requiredTagIds, $userTagIds));

            $hasRequiredDept = $event->userMeetsDepartmentRequirement($user);

            if (! $hasAllTags || ! $hasRequiredDept) {
                abort(403, 'You are not eligible to view this event\'s shifts.');
            }
        }

        $shifts = $event->shifts
            ->when($event->hide_past_shifts, fn ($shifts) => $shifts->filter(fn ($shift) => $shift->start_time->isFuture())
            )
            ->sortBy('start_time')
            ->values(); // reindex

        $userShifts = auth()->user()->shiftsForEvent($event->id)->sortBy('start_time');

        $accessibilityConflicts = collect();

        if (feature_enabled('accessibility_disclosures')) {
            $accessibilityConflicts = $shifts
                ->mapWithKeys(function (Shift $shift) use ($user): array {
                    $conflicts = array_values(array_intersect(
                        $user->accessibility_needs ?? [],
                        $shift->accessibility_conflicts ?? []
                    ));

                    return [$shift->id => $conflicts];
                })
                ->filter();
        }

        // Get all user's shifts (not just for this event) to check for conflicts
        $allUserShifts = auth()->user()->shifts()->with('event')->get();

        // Build a map of shift conflicts
        $shiftConflicts = [];
        foreach ($shifts as $shift) {
            $conflictingShifts = $allUserShifts->filter(function ($userShift) use ($shift) {
                // Skip if it's the same shift (already signed up)
                if ($userShift->id === $shift->id) {
                    return false;
                }

                // Check for time overlap
                return
                    ($userShift->start_time <= $shift->start_time && $userShift->end_time > $shift->start_time) ||
                    ($userShift->start_time < $shift->end_time && $userShift->end_time >= $shift->end_time) ||
                    ($userShift->start_time >= $shift->start_time && $userShift->end_time <= $shift->end_time);
            });

            if ($conflictingShifts->isNotEmpty()) {
                $shiftConflicts[$shift->id] = $conflictingShifts;
            }
        }

        // Calculate the time range and overlap layout needed for the agenda view
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

        if ($shifts->isEmpty()) {
            $earliestHour = 8;
            $latestHour = 18;
        }

        $earliestHour = max(0, $earliestHour - 1);
        $latestHour = min(24, $latestHour + 1);

        $shiftPositions = [];
        foreach ($shifts->groupBy(fn ($s) => $s->start_time->format('Y-m-d')) as $dayShifts) {
            foreach ($this->assignShiftColumns($dayShifts) as $shiftId => $position) {
                $shiftPositions[$shiftId] = $position;
            }
        }

        return view('events.show', [
            'event' => $event,
            'shifts' => $shifts,
            'userShifts' => $userShifts,
            'shiftConflicts' => $shiftConflicts,
            'accessibilityConflicts' => $accessibilityConflicts,
            'favoritedIds' => auth()->user()->favoritedUsers()->pluck('users.id')->all(),
            'avoidedIds' => auth()->user()->avoidedUsers()->pluck('users.id')->all(),
            'earliestHour' => $earliestHour,
            'latestHour' => $latestHour,
            'shiftPositions' => $shiftPositions,
        ]);
    }

    /**
     * Assign column positions to overlapping shifts within a single day so the
     * agenda view can lay them out side-by-side instead of stacking them.
     */
    protected function assignShiftColumns($shifts)
    {
        $sortedShifts = $shifts->sortBy('start_time')->values();

        $columns = [];
        $shiftPositions = [];

        foreach ($sortedShifts as $shift) {
            $placed = false;

            foreach ($columns as $columnIndex => $columnShifts) {
                $hasConflict = false;

                foreach ($columnShifts as $existingShift) {
                    // 1-minute buffer allows back-to-back shifts to share a column
                    if ($shift->start_time->lt($existingShift->end_time->copy()->subMinute()) &&
                        $shift->end_time->gt($existingShift->start_time->copy()->addMinute())) {
                        $hasConflict = true;
                        break;
                    }
                }

                if (! $hasConflict) {
                    $columns[$columnIndex][] = $shift;
                    $placed = true;
                    break;
                }
            }

            if (! $placed) {
                $columns[] = [$shift];
            }
        }

        $maxColumns = count($columns);

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

    public function showShift(Event $event, Shift $shift)
    {
        if ($shift->event_id !== $event->id) {
            abort(404);
        }

        $shift->load('users', 'tags');
        $event->load('requiredTags', 'requiredUserTags', 'requiredDepartments', 'requiredSectors');

        $user = auth()->user();

        $userTagIds = $user->tags()->pluck('tags.id')->toArray();
        $requiredTagIds = $event->requiredUserTags->pluck('id')->toArray();
        $hasAllTags = empty(array_diff($requiredTagIds, $userTagIds));

        $hasRequiredDepartment = $event->userMeetsDepartmentRequirement($user);

        $canSignUp = $hasAllTags && $hasRequiredDepartment;

        // Check for schedule conflicts against all of the user's other shifts
        $allUserShifts = $user->shifts()->with('event')->get();
        $conflictingShifts = $allUserShifts->filter(function ($userShift) use ($shift) {
            if ($userShift->id === $shift->id) {
                return false;
            }

            return
                ($userShift->start_time <= $shift->start_time && $userShift->end_time > $shift->start_time) ||
                ($userShift->start_time < $shift->end_time && $userShift->end_time >= $shift->end_time) ||
                ($userShift->start_time >= $shift->start_time && $userShift->end_time <= $shift->end_time);
        });

        $signedUp = $shift->users->contains($user->id);
        $isFull = $shift->users->count() >= $shift->max_volunteers;
        $isPast = $shift->start_time->isPast();
        $hasConflict = $conflictingShifts->isNotEmpty();

        // Block ineligible users from viewing the shift if the event requires eligibility.
        // Keep access for users already signed up on this shift.
        if ($event->require_eligibility && ! $canSignUp && ! $signedUp) {
            abort(403, 'You are not eligible to view this shift.');
        }

        $favoritedIds = auth()->user()->favoritedUsers()->pluck('users.id')->all();
        $avoidedIds = auth()->user()->avoidedUsers()->pluck('users.id')->all();

        return view('events.shift-show', compact(
            'event', 'shift', 'signedUp', 'isFull', 'isPast',
            'hasConflict', 'conflictingShifts', 'canSignUp',
            'favoritedIds', 'avoidedIds'
        ));
    }

    public function myShifts(Event $event)
    {
        $user = auth()->user();

        // Get all shifts for this event the user signed up for
        $shifts = $event->shifts()
            ->with('users:id,name,first_name,last_name,vol_code') // Eager load volunteers
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->orderBy('start_time')
            ->get();

        $futureShifts = $event->shifts()
            ->with('users:id,name,first_name,last_name,vol_code') // Eager load volunteers
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->where('start_time', '>=', now()) // Only future shifts
            ->orderBy('start_time')
            ->get();

        // Add up hours across all shifts
        $totalVolunteerHours = $shifts->sum(function ($shift) {
            return $shift->double_hours ? $shift->durationInHours() * 2 : $shift->durationInHours();
        });

        $shiftsRemaining = $shifts->filter(fn ($shift) => $shift->start_time->isFuture())->count();

        return view('events.my-shifts', compact('event', 'shifts', 'futureShifts', 'totalVolunteerHours', 'shiftsRemaining'));
    }

    public function myShiftsAll()
    {
        $user = auth()->user();

        // Eager load event and volunteers for each shift
        $shifts = $user->shifts()
            ->with(['event', 'users:id,name,first_name,last_name,vol_code'])
            ->orderBy('start_time')
            ->get();

        $futureShifts = $shifts->filter(fn ($shift) => $shift->start_time->isFuture());

        // Add up hours across all shifts and double the shifts that have double_hours set
        $totalVolunteerHours = $shifts->sum(function ($shift) {
            return $shift->double_hours ? $shift->durationInHours() * 2 : $shift->durationInHours();
        });

        $shiftsRemaining = $shifts->filter(fn ($shift) => $shift->start_time->isFuture())->count();

        return view('events.my-shifts-all', compact('shifts', 'futureShifts', 'totalVolunteerHours', 'shiftsRemaining'));
    }

    public function faq(Event $event)
    {
        if (! $event->faq) {
            abort(404);
        }

        return view('events.faq', compact('event'));
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\DB;

class ShiftWaitlistService
{
    /**
     * Check whether a shift's schedule conflicts with any of the user's other signed-up shifts.
     */
    public function hasConflict(Shift $shift, User $user): bool
    {
        return $user->shifts()
            ->where('shifts.id', '!=', $shift->id)
            ->where(function ($query) use ($shift) {
                $query->where(function ($q) use ($shift) {
                    $q->where('start_time', '<=', $shift->start_time)
                      ->where('end_time', '>', $shift->start_time);
                })
                ->orWhere(function ($q) use ($shift) {
                    $q->where('start_time', '<', $shift->end_time)
                      ->where('end_time', '>=', $shift->end_time);
                })
                ->orWhere(function ($q) use ($shift) {
                    $q->where('start_time', '>=', $shift->start_time)
                      ->where('end_time', '<=', $shift->end_time);
                });
            })
            ->exists();
    }

    /**
     * Returns a human-readable reason the user can't join/claim/be promoted onto this shift, or null if eligible.
     */
    public function eligibilityError(Shift $shift, User $user): ?string
    {
        $event = $shift->event()->with('requiredUserTags', 'requiredDepartments')->first();

        if ($event->requiredUserTags->isNotEmpty()) {
            $userTagIds = $user->tags()->pluck('tags.id')->toArray();
            $missingTags = $event->requiredUserTags->pluck('id')->diff($userTagIds);

            if ($missingTags->isNotEmpty()) {
                $missingTagNames = $event->requiredUserTags->whereIn('id', $missingTags)->pluck('name')->implode(', ');
                return "You must have the following tag(s) to join the waitlist for this shift: {$missingTagNames}";
            }
        }

        if ($event->requiredDepartments->isNotEmpty()) {
            $userDeptIds = $user->departments()->pluck('departments.id')->toArray();
            $requiredDeptIds = $event->requiredDepartments->pluck('id')->toArray();

            if (empty(array_intersect($requiredDeptIds, $userDeptIds))) {
                $deptNames = $event->requiredDepartments->pluck('name')->join(', ');
                return "You must be assigned to one of the following department(s) to join the waitlist: {$deptNames}";
            }
        }

        if ($this->hasConflict($shift, $user)) {
            return 'This shift conflicts with one of your other signed-up shifts.';
        }

        return null;
    }

    /**
     * Call this whenever a shift's open spot count may have changed (a signup was cancelled/removed,
     * or capacity was raised). Anyone waitlisted with auto-assign on gets signed up immediately, in
     * waitlist order; everyone else still waiting is notified that a spot is open for them to claim.
     */
    public function handleOpenSpots(Shift $shift): void
    {
        $shift->loadMissing('event');

        if ($shift->openSpots() <= 0) {
            // Spots filled back up (or never opened) — clear notification flags so the next
            // real opening notifies everyone fresh instead of being skipped as "already notified".
            $this->resetNotifications($shift);
            return;
        }

        $this->promoteAutoAssignUsers($shift);

        if ($shift->openSpots() > 0) {
            $this->notifyWaitlistOfOpening($shift);
        }
    }

    /**
     * Sign up anyone on the waitlist (in FIFO order) who has opted into auto-assign for this event,
     * skipping anyone no longer eligible (e.g. they've since picked up a conflicting shift).
     */
    public function promoteAutoAssignUsers(Shift $shift): void
    {
        $shift->loadMissing('event');
        $openSpots = $shift->openSpots();

        if ($openSpots <= 0) {
            return;
        }

        foreach ($shift->waitlistedUsers as $user) {
            if ($openSpots <= 0) {
                break;
            }

            if (! $user->pivot->auto_assign) {
                continue;
            }

            if ($this->eligibilityError($shift, $user)) {
                continue;
            }

            $shift->users()->attach($user->id, ['signed_up_at' => now()]);
            $shift->waitlistedUsers()->detach($user->id);
            $openSpots--;

            $user->notify(new AppNotification(
                title: "You're off the waitlist!",
                message: "A spot opened up on \"{$shift->name}\" for \"{$shift->event->name}\" and you've been signed up automatically.",
                url: route('volunteer.events.show', $shift->event),
            ));

            AuditLog::create([
                'action'         => 'shift_waitlist_promoted',
                'auditable_type' => Event::class,
                'auditable_id'   => $shift->event_id,
                'comment'        => "User {$user->name} auto-promoted from waitlist to {$shift->name} (Shift ID: {$shift->id})",
                'user_id'        => $user->id,
            ]);
        }
    }

    /**
     * Notify every not-yet-notified waitlisted user that a spot is open, first-come-first-served.
     * Ineligible users (conflict/tags/department) are skipped and left un-notified so they're
     * reconsidered if they later become eligible.
     */
    public function notifyWaitlistOfOpening(Shift $shift): void
    {
        $shift->loadMissing('event');

        if ($shift->openSpots() <= 0) {
            return;
        }

        $toNotify = $shift->waitlistedUsers()->wherePivotNull('notified_at')->get();

        foreach ($toNotify as $user) {
            if ($this->eligibilityError($shift, $user)) {
                continue;
            }

            $user->notify(new AppNotification(
                title: 'A spot opened up!',
                message: "A spot opened up on \"{$shift->name}\" for \"{$shift->event->name}\". It's first come, first served — claim it before someone else does.",
                url: route('volunteer.shifts.show', [$shift->event, $shift]),
            ));

            $shift->waitlistedUsers()->updateExistingPivot($user->id, ['notified_at' => now()]);
        }
    }

    /**
     * Clear the notified flag for everyone still on the waitlist so the next opening notifies fresh.
     */
    protected function resetNotifications(Shift $shift): void
    {
        $shift->waitlistedUsers()->wherePivotNotNull('notified_at')->get()->each(
            fn (User $user) => $shift->waitlistedUsers()->updateExistingPivot($user->id, ['notified_at' => null])
        );
    }

    /**
     * A waitlisted user claims an open spot. Race-safe: locks the shift row so two simultaneous
     * claims for the last spot can't both succeed.
     */
    public function claim(Shift $shift, User $user): string
    {
        return DB::transaction(function () use ($shift, $user) {
            $locked = Shift::whereKey($shift->id)->lockForUpdate()->firstOrFail();

            if (! $locked->waitlistedUsers()->where('user_id', $user->id)->exists()) {
                return 'You are not on the waitlist for this shift.';
            }

            if ($locked->openSpots() <= 0) {
                return 'Sorry, someone else already claimed that spot.';
            }

            if ($error = $this->eligibilityError($locked, $user)) {
                return $error;
            }

            $locked->users()->attach($user->id, ['signed_up_at' => now()]);
            $locked->waitlistedUsers()->detach($user->id);

            AuditLog::create([
                'action'         => 'shift_waitlist_claimed',
                'auditable_type' => Event::class,
                'auditable_id'   => $locked->event_id,
                'comment'        => "User {$user->name} claimed open spot on {$locked->name} from waitlist (Shift ID: {$locked->id})",
                'user_id'        => $user->id,
            ]);

            return '';
        });
    }

    /**
     * The user's current auto-assign preference for this event, based on their other waitlist
     * entries within it (used to pre-fill the join-waitlist checkbox). Defaults to off.
     */
    public function autoAssignDefaultForEvent(Event $event, User $user): bool
    {
        return (bool) DB::table('shift_waitlists')
            ->join('shifts', 'shifts.id', '=', 'shift_waitlists.shift_id')
            ->where('shifts.event_id', $event->id)
            ->where('shift_waitlists.user_id', $user->id)
            ->value('shift_waitlists.auto_assign');
    }

    /**
     * Apply an auto-assign preference to all of a user's current waitlist entries within an event,
     * so the setting behaves as a single event-wide choice rather than a per-shift one.
     */
    public function setAutoAssignForEvent(Event $event, User $user, bool $enabled): void
    {
        Shift::where('event_id', $event->id)
            ->whereHas('waitlistedUsers', fn ($q) => $q->where('user_id', $user->id))
            ->get()
            ->each(fn (Shift $shift) => $shift->waitlistedUsers()->updateExistingPivot($user->id, ['auto_assign' => $enabled]));
    }
}

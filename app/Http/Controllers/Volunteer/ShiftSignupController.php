<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\User;

class ShiftSignupController extends Controller
{
    public function store(Request $request, Shift $shift)
    {
        $user = $request->user();

        if ($shift->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'You are already signed up for this shift.');
        }

        if ($shift->users()->count() >= $shift->max_volunteers) {
            return back()->with('error', 'This shift is full.');
        }

        // Check if event has required tags
        $event = $shift->event()->with('requiredTags')->first();
        if ($event->requiredTags->isNotEmpty()) {
            $userTagIds = $user->tags()->pluck('tags.id')->toArray();
            $requiredTagIds = $event->requiredTags->pluck('id')->toArray();
            
            // Check if user has ALL required tags
            $missingTags = array_diff($requiredTagIds, $userTagIds);
            if (!empty($missingTags)) {
                $missingTagNames = $event->requiredTags->whereIn('id', $missingTags)->pluck('name')->toArray();
                return back()->with('error', 'You must have the following tag(s) to sign up for this event: ' . implode(', ', $missingTagNames));
            }
        }

        // Check for conflicting shifts
        $conflictingShifts = $user->shifts()
            ->where(function($query) use ($shift) {
                // Check if any of user's shifts overlap with the new shift
                $query->where(function($q) use ($shift) {
                    // New shift starts during an existing shift
                    $q->where('start_time', '<=', $shift->start_time)
                      ->where('end_time', '>', $shift->start_time);
                })
                ->orWhere(function($q) use ($shift) {
                    // New shift ends during an existing shift
                    $q->where('start_time', '<', $shift->end_time)
                      ->where('end_time', '>=', $shift->end_time);
                })
                ->orWhere(function($q) use ($shift) {
                    // New shift completely contains an existing shift
                    $q->where('start_time', '>=', $shift->start_time)
                      ->where('end_time', '<=', $shift->end_time);
                });
            })
            ->get();

        if ($conflictingShifts->isNotEmpty()) {
            $conflictDetails = $conflictingShifts->map(function($s) {
                return "{$s->event->name} - {$s->name} ({$s->start_time->format('M j, g:i A')} - {$s->end_time->format('g:i A')})";
            })->join(', ');
            
            return back()->with('error', "You cannot sign up for this shift because it conflicts with: {$conflictDetails}");
        }

        $shift->users()->attach($user->id, ['signed_up_at' => now()]);

        AuditLog::create([
            'action'         => 'shift_signup',
            'auditable_type' => Event::class,
            'auditable_id'   => $shift->event->id,
            'comment'        => "Signed up for shift {$shift->name} (Shift ID: {$shift->id})",
            'user_id'        => $user->id,
        ]);

        return back()->with('success', [
            'message' => "You've signed up for <span class=\"text-brand-green\">{$shift->name}</span> successfully",
        ]);
    }

    public function destroy(Request $request, Shift $shift)
    {
        $user = $request->user();

        $shift->users()->detach($user->id);

        AuditLog::create([
            'action'         => 'shift_dropped',
            'auditable_type' => Event::class,
            'auditable_id'   => $shift->event->id,
            'comment'        => "Dropped shift {$shift->name} (Shift ID: {$shift->id})",
            'user_id'        => $user->id,
        ]);

        return back()->with('success', [
            'message' => "You've been removed from the shift.",
        ]);
    }

    // public function storeByVolCode(Request $request, Shift $shift)
    // {
    //     // 1) Inline auth check (replace with your real logic)
    //     if (! $this->canManageShift($request->user(), $shift)) {
    //         abort(403, 'You are not allowed to manage this shift.');
    //     }

    //     // 2) Inline validation
    //     $data = $request->validate([
    //         'vol_code' => [
    //             'required','string','size:6',
    //             'regex:/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{6}$/', // unambiguous, uppercase
    //         ],
    //     ], [
    //         'vol_code.regex' => 'Vol code must be 6 characters using 2-9 and A-Z (excluding I, L, O, 0, 1).',
    //     ]);

    //     $volCode = strtoupper($data['vol_code']);

    //     // 3) Find user by vol_code
    //     $user = User::query()->where('vol_code', $volCode)->first();
    //     if (! $user) {
    //         throw ValidationException::withMessages([
    //             'vol_code' => 'No user found with that vol code.',
    //         ]);
    //     }

    //     // 4) Capacity check (optional)
    //     if (! is_null($shift->capacity)) {
    //         $count = $shift->users()->count();
    //         if ($count >= $shift->capacity) {
    //             throw ValidationException::withMessages([
    //                 'vol_code' => 'This shift is at capacity.',
    //             ]);
    //         }
    //     }

    //     // 5) Duplicate / time-conflict check
    //     $alreadyOnThisShift = $shift->users()->whereKey($user->id)->exists();

    //     $hasOverlapThisEvent = $user->shifts()
    //         ->where('event_id', $shift->event_id)
    //         ->where(function ($q) use ($shift) {
    //             $q->whereBetween('starts_at', [$shift->starts_at, $shift->ends_at])
    //               ->orWhereBetween('ends_at',   [$shift->starts_at, $shift->ends_at])
    //               ->orWhere(function ($q2) use ($shift) {
    //                   $q2->where('starts_at', '<=', $shift->starts_at)
    //                      ->where('ends_at',   '>=', $shift->ends_at);
    //               });
    //         })
    //         ->exists();

    //     if ($alreadyOnThisShift || $hasOverlapThisEvent) {
    //         throw ValidationException::withMessages([
    //             'vol_code' => 'User is already on this shift or has a time conflict.',
    //         ]);
    //     }

    //     // 6) Attach atomically (and record who added)
    //     DB::transaction(function () use ($shift, $user, $request) {
    //         $shift->users()->attach($user->id, [
    //             'added_by'   => $request->user()->id,
    //             'added_at'   => now(),
    //             'add_source' => 'quick-add-volcode',
    //         ]);
    //     });

    //     if ($request->wantsJson()) {
    //         return response()->json([
    //             'ok' => true,
    //             'message' => "Added {$user->name} to shift.",
    //             'user' => [
    //                 'id' => $user->id,
    //                 'name' => $user->name,
    //                 'vol_code' => $user->vol_code,
    //             ],
    //         ]);
    //     }

    //     return back()->with('status', "Added {$user->name} to shift.");
    // }

    /** Replace this with your real role/permission logic */
    private function canManageShift(?User $actor, Shift $shift): bool
    {
        if (! $actor) return false;

        // Minimal default:
        return method_exists($actor, 'isAdmin') ? $actor->isAdmin() : true;
    }

}

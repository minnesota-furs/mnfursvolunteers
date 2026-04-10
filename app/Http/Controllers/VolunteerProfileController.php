<?php

namespace App\Http\Controllers;

use App\Models\User;

class VolunteerProfileController extends Controller
{
    public function show(User $user)
    {
        $authUser = auth()->user();
        $relationship = $authUser->getRelationshipWith($user->id);

        $user->load(['departments.sector', 'tags', 'recognitions' => function ($q) {
            $q->where('is_private', false)->latest()->limit(5);
        }]);

        // Get upcoming shifts for this user (public info)
        $upcomingShifts = $user->shifts()
            ->where('start_time', '>=', now())
            ->with('event')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        return view('volunteer-profile.show', compact('user', 'relationship', 'upcomingShifts'));
    }
}

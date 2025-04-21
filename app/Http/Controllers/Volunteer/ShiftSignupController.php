<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;

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

        $shift->users()->attach($user->id, ['signed_up_at' => now()]);

        return back()->with('success', [
            'message' => "You've signed up for <span class=\"text-brand-green\">{$shift->name}</span> successfully",
        ]);
    }

    public function destroy(Request $request, Shift $shift)
    {
        $user = $request->user();

        $shift->users()->detach($user->id);

        return back()->with('success', [
            'message' => "You've been removed from the shift.",
        ]);
    }
}

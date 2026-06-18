<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRelationship;
use Illuminate\Http\Request;

class UserRelationshipController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $favorites = $user->favoritedUsers()->orderBy('first_name')->get();
        $avoided = $user->avoidedUsers()->orderBy('first_name')->get();

        return view('relationships.index', compact('favorites', 'avoided'));
    }

    public function toggle(Request $request, User $user)
    {
        $request->validate([
            'type' => ['required', 'in:favorite,avoid'],
        ]);

        $authUser = auth()->user();

        if ($authUser->id === $user->id) {
            return back()->with('error', 'You cannot favorite or avoid yourself.');
        }

        $type = $request->input('type');
        $existing = $authUser->relationships()
            ->where('target_user_id', $user->id)
            ->first();

        if ($existing && $existing->type === $type) {
            // Same type — remove the relationship (toggle off)
            $existing->delete();
            return back()->with('success', "Removed {$type} mark from {$user->displayName()}.");
        }

        // Upsert: create or change the type
        UserRelationship::updateOrCreate(
            ['user_id' => $authUser->id, 'target_user_id' => $user->id],
            ['type' => $type]
        );

        $label = $type === 'favorite' ? 'favorited' : 'marked as avoid';
        return back()->with('success', "{$user->displayName()} has been {$label}.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Recognition;
use App\Models\User;
use Illuminate\Http\Request;

class RecognitionController extends Controller
{
    /**
     * Display recognitions for the authenticated user.
     */
    public function myRecognitions()
    {
        if (!\App\Models\ApplicationSetting::get('feature_recognition', false)) {
            abort(404, 'Recognition feature is not enabled.');
        }

        $user = auth()->user();
        $recognitions = $user->recognitions()
            ->orderBy('date', 'desc')
            ->get();

        return view('recognition.my-recognitions', compact('recognitions', 'user'));
    }

    /**
     * Display recognitions for a specific user (public view).
     */
    public function showUserRecognitions(User $user)
    {
        if (!\App\Models\ApplicationSetting::get('feature_recognition', false)) {
            abort(404, 'Recognition feature is not enabled.');
        }

        // Get recognitions visible to the current user
        $recognitions = $user->recognitions()
            ->visible(auth()->user())
            ->orderBy('date', 'desc')
            ->get();

        return view('recognition.user-recognitions', compact('recognitions', 'user'));
    }
}

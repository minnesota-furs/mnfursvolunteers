<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use Corcel\Model\User as WordPressUser;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // dd($request->user());
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function linkWordPress(Request $request)
    {
        \Log::debug('hey');
        $request->validate([
            'wordpress_email' => 'required|string',
            'wordpress_password' => 'required|string',
        ]);

        // Attempt to find WordPress user
        $wpUser = WordPressUser::where('user_email', $request->wordpress_email)->first();

        if (!$wpUser || !app('hash')->check($request->wordpress_password, $wpUser->user_pass)) {
            \Log::debug('errors 1');
            return back()->withErrors(['wordpress_email' => 'Invalid WordPress credentials.']);
        }

        // Link WordPress user
        $user = auth()->user();
        $user->update([
            'wordpress_id' => $wpUser->ID,
        ]);

        return back()->with('success', [
            'message' => 'WordPress account linked successfully.',
        ]);
    }

    public function unlinkWordPress()
    {
        $user = auth()->user();
        $user->update([
            'wordpress_id' => null,
        ]);

        return back()->with('success', [
            'message' => 'WordPress account unlinked.',
        ]);
    }
}

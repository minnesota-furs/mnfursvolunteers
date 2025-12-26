<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
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

    /**
     * Update the user's email preferences.
     */
    public function updateEmailPreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'email_shift_reminders' => 'nullable|boolean',
            'email_event_updates' => 'nullable|boolean',
            'email_hour_approvals' => 'nullable|boolean',
            'email_election_reminders' => 'nullable|boolean',
        ]);

        $user = $request->user();
        
        // Checkboxes not checked won't be in the request, so we need to handle that
        $user->update([
            'email_shift_reminders' => $request->has('email_shift_reminders'),
            'email_event_updates' => $request->has('email_event_updates'),
            'email_hour_approvals' => $request->has('email_hour_approvals'),
            'email_election_reminders' => $request->has('email_election_reminders'),
        ]);

        return Redirect::route('profile.edit')->with('email-preferences-status', 'preferences-updated');
    }

    /**
     * Unsubscribe a user from election reminder emails
     */
    public function unsubscribeElections(User $user, string $token)
    {
        // Verify the token matches the user's email (simple security measure)
        $expectedToken = md5($user->email . config('app.key'));
        
        if ($token !== $expectedToken) {
            abort(403, 'Invalid unsubscribe link');
        }

        // Update the user's preferences
        $user->update([
            'email_election_reminders' => false,
        ]);

        return view('profile.unsubscribed', [
            'user' => $user,
            'preferenceType' => 'election reminders',
        ]);
    }
}

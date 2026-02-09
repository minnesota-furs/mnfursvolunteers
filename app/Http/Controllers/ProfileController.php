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
        $user = $request->user()->load('customFieldValues');
        
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Update basic profile fields
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Handle user-editable custom fields
        $customFields = \App\Models\CustomField::active()->userEditable()->get();
        
        foreach ($customFields as $field) {
            $fieldKey = 'custom_field_' . $field->id;
            $value = $request->input($fieldKey);
            
            // Handle checkbox fields (convert array to comma-separated string)
            if ($field->field_type === 'checkbox' && is_array($value)) {
                $value = implode(',', $value);
            }
            
            // If value is empty or null, delete the custom field value
            if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
                \App\Models\CustomFieldValue::where('user_id', $user->id)
                    ->where('custom_field_id', $field->id)
                    ->delete();
            } else {
                // Otherwise, update or create the custom field value
                \App\Models\CustomFieldValue::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'custom_field_id' => $field->id,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }

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

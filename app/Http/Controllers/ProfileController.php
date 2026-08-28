<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessibilityNeedsUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\User;
use App\Services\ConcatService;
use App\Services\ConcatSyncService;
use Corcel\Model\User as WordPressUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['customFieldValues', 'departments.sector', 'headDepartments:id', 'concatRoleGrants.sector.concatRoleMapping']);
        $timezones = grouped_timezones();

        return view('profile.edit', [
            'user' => $user,
            'timezones' => $timezones,
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
        $customFields = CustomField::active()->userEditable()->get();

        foreach ($customFields as $field) {
            $fieldKey = 'custom_field_'.$field->id;
            $value = $request->input($fieldKey);

            // Handle checkbox fields (convert array to comma-separated string)
            if ($field->field_type === 'checkbox' && is_array($value)) {
                $value = implode(',', $value);
            }

            // If value is empty or null, delete the custom field value
            if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
                CustomFieldValue::where('user_id', $user->id)
                    ->where('custom_field_id', $field->id)
                    ->delete();
            } else {
                // Otherwise, update or create the custom field value
                CustomFieldValue::updateOrCreate(
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

    public function updateAccessibilityNeeds(AccessibilityNeedsUpdateRequest $request): RedirectResponse
    {
        abort_unless(feature_enabled('accessibility_disclosures'), 404);

        $request->user()->update([
            'accessibility_needs' => $request->validated('accessibility_needs'),
        ]);

        return Redirect::route('profile.edit')->with('accessibility-needs-status', 'accessibility-needs-updated');
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

    /**
     * Show the required custom fields screen for users who have missing force_set fields.
     */
    public function requiredFields(Request $request): View
    {
        $user = $request->user()->load('customFieldValues');

        $fields = CustomField::active()
            ->where('force_set', true)
            ->ordered()
            ->get()
            ->filter(function (CustomField $field) use ($user) {
                $value = $user->customFieldValues
                    ->firstWhere('custom_field_id', $field->id)
                    ?->value;

                return is_null($value) || $value === '';
            });

        // If nothing is missing, send them to the dashboard
        if ($fields->isEmpty()) {
            return redirect()->route('dashboard');
        }

        return view('profile.required-fields', compact('user', 'fields'));
    }

    /**
     * Save the force_set custom field values provided by the user.
     */
    public function saveRequiredFields(Request $request): RedirectResponse
    {
        $user = $request->user()->load('customFieldValues');

        // Only process fields that are still missing a value — same filter as the GET view
        $fields = CustomField::active()
            ->where('force_set', true)
            ->ordered()
            ->get()
            ->filter(function (CustomField $field) use ($user) {
                $value = $user->customFieldValues
                    ->firstWhere('custom_field_id', $field->id)
                    ?->value;

                return is_null($value) || $value === '';
            });

        // Build validation rules for each missing force_set field
        $rules = [];
        foreach ($fields as $field) {
            $fieldKey = 'custom_field_'.$field->id;
            $rules[$fieldKey] = 'required';
        }

        $request->validate($rules);

        foreach ($fields as $field) {
            $fieldKey = 'custom_field_'.$field->id;
            $value = $request->input($fieldKey);

            if ($field->field_type === 'checkbox' && is_array($value)) {
                $value = implode(',', $value);
            }

            if (! is_null($value) && $value !== '') {
                CustomFieldValue::updateOrCreate(
                    ['user_id' => $user->id, 'custom_field_id' => $field->id],
                    ['value' => $value]
                );
            }
        }

        return redirect()->intended(route('dashboard'));
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

        if (! $wpUser || ! app('hash')->check($request->wordpress_password, $wpUser->user_pass)) {
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
     * Update the user's preferred timezone for viewing dates/times.
     */
    public function updateTimezone(Request $request): RedirectResponse
    {
        $request->validate([
            'timezone' => ['nullable', 'timezone'],
        ]);

        $request->user()->update([
            'timezone' => $request->input('timezone') ?: null,
        ]);

        return Redirect::route('profile.edit')->with('timezone-status', 'timezone-updated');
    }

    /**
     * Generate a Telegram link token and show the deep link / QR code to the user.
     */
    public function linkTelegram(Request $request): RedirectResponse
    {
        $request->user()->generateTelegramLinkToken();

        return Redirect::route('profile.edit')->with('status', 'telegram-link-generated');
    }

    /**
     * Poll for whether the pending Telegram link has been completed
     * (the user opened the deep link and pressed Start in Telegram).
     */
    public function telegramStatus(Request $request)
    {
        $user = $request->user()->fresh();

        return response()->json([
            'linked' => $user->hasTelegramLinked(),
            'username' => $user->telegram_username,
        ]);
    }

    public function unlinkTelegram(Request $request): RedirectResponse
    {
        $request->user()->unlinkTelegram();

        return Redirect::route('profile.edit')->with('success', [
            'message' => 'Telegram account unlinked.',
        ]);
    }

    /**
     * Numeric codes shown to the user alongside a deliberately vague error
     * message, so support staff can look up what actually went wrong without
     * the message itself revealing the verification logic (e.g. that a
     * different-email link is rejected specifically for a legal-name
     * mismatch, which would invite people to fish for valid combinations).
     *
     * 1001 - ConCat integration not configured
     * 1002 - No ConCat account found for the given email
     * 1003 - Different-email link attempted with no legal name on file
     * 1004 - Different-email link's legal name didn't match ConCat's
     */
    private const CONCAT_ERROR_NOT_CONFIGURED = 1001;

    private const CONCAT_ERROR_NO_MATCH = 1002;

    private const CONCAT_ERROR_NO_LEGAL_NAME = 1003;

    private const CONCAT_ERROR_NAME_MISMATCH = 1004;

    /**
     * Self-service ConCat link: tries the volunteer's own account email by
     * default, or a different email they provide (their ConCat registration
     * may use a personal email that differs from their volunteer account).
     */
    public function linkConcat(Request $request): RedirectResponse
    {
        $concat = app(ConcatService::class);

        if (! $concat->isConfigured()) {
            return $this->concatLinkError($request, self::CONCAT_ERROR_NOT_CONFIGURED);
        }

        $request->validate([
            'concat_search_email' => ['nullable', 'email'],
        ]);

        $user = $request->user();
        $usingDifferentEmail = $request->filled('concat_search_email');
        $email = $usingDifferentEmail ? $request->input('concat_search_email') : $user->email;

        $match = $concat->findUserByEmail($email);

        if (! $match) {
            return $this->concatLinkError($request, self::CONCAT_ERROR_NO_MATCH);
        }

        // A different email means we can't trust it belongs to this volunteer just because
        // ConCat returned a match — require the legal name on file to agree too, so a
        // self-service link can't accidentally (or deliberately) grab someone else's account.
        if ($usingDifferentEmail) {
            if (empty($user->first_name) || empty($user->last_name)) {
                return $this->concatLinkError($request, self::CONCAT_ERROR_NO_LEGAL_NAME);
            }

            $nameMatches = strcasecmp(trim($user->first_name), trim($match['firstName'])) === 0
                && strcasecmp(trim($user->last_name), trim($match['lastName'])) === 0;

            if (! $nameMatches) {
                return $this->concatLinkError($request, self::CONCAT_ERROR_NAME_MISMATCH);
            }
        }

        app(ConcatSyncService::class)->associateUser($user, $match['id']);

        return $this->toConcatSection()->with('success', [
            'message' => "Linked to ConCat account: {$match['firstName']} {$match['lastName']} ({$match['email']}).",
        ]);
    }

    /**
     * Redirect back to the Concat section with a deliberately vague error and
     * a lookup code, preserving whichever form the volunteer used.
     */
    private function concatLinkError(Request $request, int $code): RedirectResponse
    {
        return $this->toConcatSection()
            ->withInput($request->only('concat_search_email'))
            ->with('error', "Problem linking accounts. Error: {$code}");
    }

    public function unlinkConcat(Request $request): RedirectResponse
    {
        app(ConcatSyncService::class)->disassociateUser($request->user());

        return $this->toConcatSection()->with('success', [
            'message' => 'ConCat account unlinked.',
        ]);
    }

    /**
     * Redirect back to the profile page scrolled to the Concat section,
     * since these actions all originate from forms inside it.
     */
    private function toConcatSection(): RedirectResponse
    {
        return Redirect::to(route('profile.edit').'#concat');
    }

    /**
     * Unsubscribe a user from election reminder emails
     */
    public function unsubscribeElections(User $user, string $token)
    {
        // Verify the token matches the user's email (simple security measure)
        $expectedToken = md5($user->email.config('app.key'));

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

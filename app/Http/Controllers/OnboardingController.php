<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessibilityNeedsUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * The wizard steps, in order. Telegram is only included when the app has a bot configured.
     */
    protected function steps(): array
    {
        $steps = ['profile', 'accessibility', 'timezone', 'calendar'];

        if (app_setting('telegram_bot_username')) {
            $steps[] = 'telegram';
        }

        return $steps;
    }

    /**
     * Show the onboarding wizard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $steps = $this->steps();

        $step = max(1, min((int) $request->query('step', 1), count($steps)));
        $currentStep = $steps[$step - 1];

        $timezones = grouped_timezones();

        return view('onboarding.index', [
            'user' => $user,
            'steps' => $steps,
            'step' => $step,
            'currentStep' => $currentStep,
            'totalSteps' => count($steps),
            'timezones' => $timezones,
        ]);
    }

    /**
     * Step 1: confirm/correct legal name and pronouns.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'pronouns' => ['nullable', 'string', 'max:50'],
        ]);

        $request->user()->update($request->only('first_name', 'last_name', 'pronouns'));

        return $this->redirectToStep(2);
    }

    /**
     * Step 2: optionally disclose accessibility needs.
     */
    public function updateAccessibilityNeeds(AccessibilityNeedsUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'accessibility_needs' => $request->validated('accessibility_needs'),
        ]);

        return $this->redirectToStep(3);
    }

    /**
     * Step 3: confirm/change the timezone they view dates and times in.
     */
    public function updateTimezone(Request $request): RedirectResponse
    {
        $request->validate([
            'timezone' => ['nullable', 'timezone'],
        ]);

        $request->user()->update([
            'timezone' => $request->input('timezone') ?: null,
        ]);

        return $this->redirectToStep(4);
    }

    /**
     * Step 4: generate the user's personal iCal calendar feed.
     */
    public function generateCalendar(Request $request): RedirectResponse
    {
        $request->user()->generateCalendarToken();

        return $this->redirectToStep(4);
    }

    /**
     * Step 5: generate a Telegram link token and show the deep link / QR code.
     */
    public function linkTelegram(Request $request): RedirectResponse
    {
        $request->user()->generateTelegramLinkToken();

        return $this->redirectToStep(5, ['status' => 'telegram-link-generated']);
    }

    /**
     * Poll for whether the pending Telegram link has been completed.
     */
    public function telegramStatus(Request $request)
    {
        $user = $request->user()->fresh();

        return response()->json([
            'linked' => $user->hasTelegramLinked(),
            'username' => $user->telegram_username,
        ]);
    }

    /**
     * Mark the wizard complete and send the user to their dashboard.
     */
    public function finish(Request $request): RedirectResponse
    {
        $request->user()->update(['onboarded_at' => now()]);

        return redirect()->route('dashboard')->with('status', 'onboarding-complete');
    }

    protected function redirectToStep(int $step, array $params = []): RedirectResponse
    {
        return redirect()->route('onboarding.index', array_merge(['step' => $step], $params));
    }
}

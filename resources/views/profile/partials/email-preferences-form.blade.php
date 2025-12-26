<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Email Preferences') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Manage which email notifications you would like to receive.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.email-preferences') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4">
            <!-- Shift Reminders -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="email_shift_reminders" 
                           name="email_shift_reminders" 
                           type="checkbox" 
                           value="1"
                           {{ old('email_shift_reminders', $user->email_shift_reminders) ? 'checked' : '' }}
                           class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-brand-green dark:ring-offset-gray-800">
                </div>
                <div class="ml-3 text-sm">
                    <label for="email_shift_reminders" class="font-medium text-gray-900 dark:text-gray-100">
                        Shift Reminders
                    </label>
                    <p class="text-gray-600 dark:text-gray-400">
                        Receive daily email reminders about your upcoming volunteer shifts at 8:00 AM.
                    </p>
                </div>
            </div>

            <!-- Event Updates -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="email_event_updates" 
                           name="email_event_updates" 
                           type="checkbox" 
                           value="1"
                           {{ old('email_event_updates', $user->email_event_updates) ? 'checked' : '' }}
                           class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-brand-green dark:ring-offset-gray-800">
                </div>
                <div class="ml-3 text-sm">
                    <label for="email_event_updates" class="font-medium text-gray-900 dark:text-gray-100">
                        Event Updates
                    </label>
                    <p class="text-gray-600 dark:text-gray-400">
                        Get notified when events you're signed up for are updated or cancelled.
                    </p>
                </div>
            </div>

            <!-- Hour Approvals -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="email_hour_approvals" 
                           name="email_hour_approvals" 
                           type="checkbox" 
                           value="1"
                           {{ old('email_hour_approvals', $user->email_hour_approvals) ? 'checked' : '' }}
                           class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-brand-green dark:ring-offset-gray-800">
                </div>
                <div class="ml-3 text-sm">
                    <label for="email_hour_approvals" class="font-medium text-gray-900 dark:text-gray-100">
                        Hour Approvals
                    </label>
                    <p class="text-gray-600 dark:text-gray-400">
                        Receive notifications when your volunteer hours are approved or need changes.
                    </p>
                </div>
            </div>

            <!-- Election Reminders -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="email_election_reminders" 
                           name="email_election_reminders" 
                           type="checkbox" 
                           value="1"
                           {{ old('email_election_reminders', $user->email_election_reminders) ? 'checked' : '' }}
                           class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-brand-green dark:ring-offset-gray-800">
                </div>
                <div class="ml-3 text-sm">
                    <label for="email_election_reminders" class="font-medium text-gray-900 dark:text-gray-100">
                        Election Updates & Reminders
                    </label>
                    <p class="text-gray-600 dark:text-gray-400">
                        Get notified about upcoming elections and reminders to vote if you're eligible.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-green-dark focus:bg-brand-green-dark active:bg-brand-green-dark focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Save Preferences') }}
            </button>

            @if (session('email-preferences-status') === 'preferences-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

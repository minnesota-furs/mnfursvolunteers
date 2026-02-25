<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Calendar Subscription') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Subscribe to your volunteer shifts in Google Calendar, Apple Calendar, or any other calendar app that supports iCal feeds. The URL below is private — anyone with it can view your shifts.
        </p>
    </header>

    @if ($user->calendar_token)
        <div class="mt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Your personal calendar feed URL
                </label>
                <div class="flex items-center gap-2">
                    <input
                        id="calendar-url"
                        type="text"
                        readonly
                        value="{{ route('calendar.shifts', $user->calendar_token) }}"
                        class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 focus:outline-none"
                        onclick="this.select()"
                    />
                    <button
                        type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('calendar-url').value).then(() => { this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 2000); })"
                        class="shrink-0 rounded-md bg-gray-200 dark:bg-gray-600 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-500"
                    >
                        Copy
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="https://calendar.google.com/calendar/r?cid={{ urlencode(route('calendar.shifts', $user->calendar_token)) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Add to Google Calendar
                </a>
                <a
                    href="webcal://{{ parse_url(route('calendar.shifts', $user->calendar_token), PHP_URL_HOST) }}{{ parse_url(route('calendar.shifts', $user->calendar_token), PHP_URL_PATH) }}"
                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-800 dark:bg-gray-600 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700"
                >
                    <x-heroicon-s-calendar class="w-4 h-4 inline -mt-0.5 mr-1" />
                    Add to Apple / Outlook Calendar
                </a>
            </div>
        </div>
    @else
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            You don't have a calendar feed yet. Generate one below.
        </p>
    @endif

    <form method="POST" action="{{ route('profile.calendar-token') }}" class="mt-6">
        @csrf
        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green"
                onclick="{{ $user->calendar_token ? 'return confirm(\'Regenerating the URL will break any existing calendar subscriptions. Continue?\')' : '' }}"
            >
                {{ $user->calendar_token ? 'Regenerate Calendar URL' : 'Generate Calendar URL' }}
            </button>

            @if (session('status') === 'calendar-token-regenerated')
                <p class="text-sm text-green-600 dark:text-green-400">Calendar URL updated.</p>
            @endif
        </div>
    </form>
</section>

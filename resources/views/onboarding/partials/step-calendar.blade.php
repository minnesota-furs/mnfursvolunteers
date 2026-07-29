@php
    $hasNextStep = $step < $totalSteps;
    $nextUrl = $hasNextStep ? route('onboarding.index', ['step' => $step + 1]) : null;
@endphp

<header>
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ __('Subscribe to your shifts') }}
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Generate a personal calendar link and subscribe in Google Calendar, Apple Calendar, or Outlook to see your volunteer shifts alongside everything else.') }}
    </p>
</header>

@if ($user->calendar_token)
    <div class="mt-4 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Your personal calendar feed URL') }}
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
                    {{ __('Copy') }}
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="https://calendar.google.com/calendar/r?cid={{ urlencode(route('calendar.shifts', $user->calendar_token)) }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                {{ __('Add to Google Calendar') }}
            </a>
            <a
                href="webcal://{{ parse_url(route('calendar.shifts', $user->calendar_token), PHP_URL_HOST) }}{{ parse_url(route('calendar.shifts', $user->calendar_token), PHP_URL_PATH) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-800 dark:bg-gray-600 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
                <x-heroicon-s-calendar class="w-4 h-4 inline -mt-0.5 mr-1" />
                {{ __('Add to Apple / Outlook Calendar') }}
            </a>
        </div>
    </div>
@else
    <form method="post" action="{{ route('onboarding.calendar') }}" class="mt-4">
        @csrf
        <button
            type="submit"
            class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700"
        >
            {{ __('Generate my calendar link') }}
        </button>
    </form>
@endif

<div class="mt-8 flex justify-between items-center">
    @unless ($user->calendar_token)
        @if ($hasNextStep)
            <a href="{{ $nextUrl }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                {{ __('Skip for now') }}
            </a>
        @else
            <form method="post" action="{{ route('onboarding.finish') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    {{ __('Skip for now') }}
                </button>
            </form>
        @endif
    @else
        <span></span>
    @endunless

    @if ($hasNextStep)
        <a href="{{ $nextUrl }}" class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            {{ __('Continue') }}
        </a>
    @else
        <form method="post" action="{{ route('onboarding.finish') }}">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                {{ __('Finish') }}
            </button>
        </form>
    @endif
</div>

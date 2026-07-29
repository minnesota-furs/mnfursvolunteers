<section class="{{ $user->timezone ? 'lg:flex lg:items-start lg:gap-10' : '' }}">
    <div class="max-w-xl">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Timezone') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Choose the timezone dates and times should be displayed in throughout the site. Leave as "Use application default" to see times the same way ' . app_name() . ' staff do.') }}
            </p>
        </header>

        <form method="post" action="{{ route('profile.timezone') }}" class="mt-6 space-y-6">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="timezone" :value="__('Timezone')" />
                @php $selectedTimezone = old('timezone', $user->timezone); @endphp
                <select name="timezone" id="timezone"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Use application default ({{ app_timezone() }})</option>
                    <optgroup label="Common">
                        @foreach(common_timezones() as $tz)
                            <option value="{{ $tz }}" {{ $selectedTimezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    @foreach($timezones as $region => $zones)
                        <optgroup label="{{ $region }}">
                            @foreach($zones as $tz)
                                <option value="{{ $tz }}" {{ $selectedTimezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>

                @if (session('timezone-status') === 'timezone-updated')
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
    </div>

    @if($user->timezone)
        <aside class="mt-6 lg:mt-0 lg:w-72 lg:flex-shrink-0 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200 flex items-center gap-1.5">
                <x-heroicon-m-information-circle class="w-4 h-4 flex-shrink-0" />
                {{ __('What to look for') }}
            </h3>
            <p class="mt-2 text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                {{ __('Since your timezone is different from ' . app_name() . "'s default (" . app_timezone() . '), times that have been converted to your timezone are shown underlined with a globe icon, like this:') }}
            </p>
            <p class="mt-3">
                <x-user-time :time="now()" format="M j, Y g:i A" />
            </p>
            <p class="mt-3 text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                {{ __('Hover (or tap on mobile) over one of these to see a popover with the time in ' . app_name() . "'s native timezone.") }}
            </p>
        </aside>
    @endif
</section>

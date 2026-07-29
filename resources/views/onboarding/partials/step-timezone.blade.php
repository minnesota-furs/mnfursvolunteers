<div x-data="onboardingTimezone({ appTimezone: @js(app_timezone()), current: @js(old('timezone', $user->timezone)) })" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('What timezone are you in?') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __(app_name() . ' operates in ' . app_timezone() . '. If you\'re somewhere else, choose your timezone so dates and times are shown correctly for you.') }}
        </p>
    </header>

    <template x-if="showSuggestion">
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-md border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
            <p class="text-sm text-blue-800 dark:text-blue-300">
                {{ __('Looks like your device is set to') }} <strong x-text="detected"></strong>{{ __(', which is different from ' . app_name() . "'s default (" . app_timezone() . ').') }}
            </p>
            <button type="button" @click="applyDetected()" class="shrink-0 rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                {{ __('Use my device\'s timezone') }}
            </button>
        </div>
    </template>

    <form method="post" action="{{ route('onboarding.timezone') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="timezone" :value="__('Timezone')" />
            <select name="timezone" id="timezone" x-model="selected"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">{{ __('Use application default (' . app_timezone() . ')') }}</option>
                <optgroup label="Common">
                    @foreach(common_timezones() as $tz)
                        <option value="{{ $tz }}">{{ $tz }}</option>
                    @endforeach
                </optgroup>
                @foreach($timezones as $region => $zones)
                    <optgroup label="{{ $region }}">
                        @foreach($zones as $tz)
                            <option value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('onboarding.index', ['step' => 3]) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                {{ __('Skip for now') }}
            </a>
            <x-primary-button>{{ __('Save & Continue') }}</x-primary-button>
        </div>
    </form>
</div>

<script>
    function onboardingTimezone({ appTimezone, current }) {
        return {
            appTimezone,
            selected: current || '',
            detected: null,
            showSuggestion: false,

            init() {
                try {
                    this.detected = Intl.DateTimeFormat().resolvedOptions().timeZone;
                } catch (e) {
                    this.detected = null;
                }

                if (this.detected && this.detected !== this.appTimezone && !this.selected) {
                    this.showSuggestion = true;
                }
            },

            applyDetected() {
                this.selected = this.detected;
                this.showSuggestion = false;
            },
        };
    }
</script>

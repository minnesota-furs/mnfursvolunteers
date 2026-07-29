<header>
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ __('Let\'s double-check your info') }}
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('We\'re almost done! But we just want to get a few things out of the way real fast.Do') }}
    </p>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Make sure your legal name and pronouns are correct. Staff use these when scheduling, printing name tags and other administrative functions.') }}
    </p>
</header>

<form method="post" action="{{ route('onboarding.profile') }}" class="mt-6 space-y-6">
    @csrf

    <div>
        <x-input-label for="first_name" :value="__('Legal First Name')" />
        <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
    </div>

    <div>
        <x-input-label for="last_name" :value="__('Legal Last Name')" />
        <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
    </div>

    @php
        $presetPronouns = ['he/him', 'she/her', 'they/them', 'Any'];
        $currentPronouns = old('pronouns', $user->pronouns) ?? '';
        $isPreset = in_array($currentPronouns, $presetPronouns, true);
    @endphp
    <div x-data="{ pronouns: @js($currentPronouns), mode: @js($isPreset ? $currentPronouns : ($currentPronouns !== '' ? 'other' : '')) }">
        <x-input-label for="pronouns" :value="__('Pronouns')" />
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach($presetPronouns as $preset)
                <button type="button"
                    @click="mode = @js($preset); pronouns = @js($preset)"
                    :class="mode === @js($preset) ? 'bg-brand-green text-white border-brand-green' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
                    class="px-3 py-1.5 rounded-full border text-sm font-medium transition"
                >{{ $preset }}</button>
            @endforeach
            <button type="button"
                @click="mode = 'other'"
                :class="mode === 'other' ? 'bg-brand-green text-white border-brand-green' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
                class="px-3 py-1.5 rounded-full border text-sm font-medium transition"
            >{{ __('Other') }}</button>
        </div>

        <div class="mt-3" x-show="mode === 'other'" x-cloak>
            <x-text-input
                id="pronouns"
                type="text"
                class="block w-full"
                placeholder="{{ __('Type your own') }}"
                x-model="pronouns"
                @focus="mode = 'other'"
            />
        </div>

        <input type="hidden" name="pronouns" :value="pronouns">
        <x-input-error class="mt-2" :messages="$errors->get('pronouns')" />
    </div>

    <div class="flex justify-end">
        <x-primary-button>{{ __('Save & Continue') }}</x-primary-button>
    </div>
</form>

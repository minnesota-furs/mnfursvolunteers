@props(['user'])

@php
    $selectedNeeds = old('accessibility_needs', $user->accessibility_needs ?? []);
    $hasAccessibilityNeeds = old('has_accessibility_needs', count($selectedNeeds) > 0 ? '1' : '0');
@endphp

<div x-data="{ hasNeeds: @js((string) $hasAccessibilityNeeds) }" class="space-y-4">
    <fieldset>
        <legend class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ __('Do you have accessibility needs you would like us to know about?') }}
        </legend>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="radio" name="has_accessibility_needs" value="0" x-model="hasNeeds"
                    class="border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                {{ __('No Accessibility Needs') }}
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="radio" name="has_accessibility_needs" value="1" x-model="hasNeeds"
                    class="border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                {{ __('I have accessibility needs') }}
            </label>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('has_accessibility_needs')" />
    </fieldset>

    <fieldset x-show="hasNeeds === '1'" x-cloak>
        <legend class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ __('Select all that apply') }}
        </legend>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach(\App\Models\User::ACCESSIBILITY_NEEDS as $need)
                <label class="flex items-start gap-2 rounded-md border border-gray-200 p-3 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="accessibility_needs[]" value="{{ $need }}"
                        @checked(in_array($need, $selectedNeeds, true))
                        class="mt-0.5 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                    <span>{{ __($need) }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('accessibility_needs')" />
        <x-input-error class="mt-2" :messages="$errors->get('accessibility_needs.*')" />
    </fieldset>
</div>

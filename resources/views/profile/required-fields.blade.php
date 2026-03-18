<x-app-layout>
    @section('title', 'Complete Your Profile')
    <x-slot name="header">
        {{ __('Complete Your Profile') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <p class="text-gray-700 dark:text-gray-300">
                            Before you continue, please fill in the following required information.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('profile.required-fields.save') }}">
                        @csrf

                        @foreach($fields as $field)
                            <div class="mb-6">
                                <x-input-label for="custom_field_{{ $field->id }}" :value="$field->name . ' *'" />

                                @php
                                    $value = old('custom_field_' . $field->id, '');
                                @endphp

                                @if($field->field_type === 'text')
                                    <x-text-input
                                        name="custom_field_{{ $field->id }}"
                                        id="custom_field_{{ $field->id }}"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :value="$value"
                                        required />

                                @elseif($field->field_type === 'textarea')
                                    <x-textarea-input
                                        name="custom_field_{{ $field->id }}"
                                        id="custom_field_{{ $field->id }}"
                                        rows="4"
                                        class="mt-1 block w-full"
                                        required>{{ $value }}</x-textarea-input>

                                @elseif($field->field_type === 'select')
                                    <x-select-input
                                        name="custom_field_{{ $field->id }}"
                                        id="custom_field_{{ $field->id }}"
                                        class="mt-1 block w-full"
                                        required>
                                        <option value="">Select an option...</option>
                                        @foreach($field->options ?? [] as $option)
                                            <option value="{{ $option }}" {{ $value == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </x-select-input>

                                @elseif($field->field_type === 'checkbox')
                                    <div class="mt-2 space-y-2">
                                        @foreach($field->options ?? [] as $option)
                                            @php
                                                $checkedValues = is_array($value) ? $value : explode(',', $value);
                                            @endphp
                                            <label class="flex items-center space-x-2">
                                                <input
                                                    type="checkbox"
                                                    name="custom_field_{{ $field->id }}[]"
                                                    value="{{ $option }}"
                                                    {{ in_array($option, $checkedValues) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-700 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50 dark:bg-gray-900 dark:focus:ring-green-600 dark:focus:ring-opacity-50">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($field->field_type === 'date')
                                    <x-text-input
                                        name="custom_field_{{ $field->id }}"
                                        id="custom_field_{{ $field->id }}"
                                        type="date"
                                        class="mt-1 block w-full"
                                        :value="$value"
                                        required />

                                @elseif($field->field_type === 'number')
                                    <x-text-input
                                        name="custom_field_{{ $field->id }}"
                                        id="custom_field_{{ $field->id }}"
                                        type="number"
                                        class="mt-1 block w-full"
                                        :value="$value"
                                        required />
                                @endif

                                @if($field->description)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $field->description }}</p>
                                @endif

                                <x-input-error class="mt-2" :messages="$errors->get('custom_field_' . $field->id)" />
                            </div>
                        @endforeach

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Save &amp; Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

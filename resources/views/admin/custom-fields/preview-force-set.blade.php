<x-app-layout>
    @section('title', 'Preview: Force Set Screen')
    <x-slot name="header">
        {{ __('Preview: Force Set Screen') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.custom-fields.index') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            &larr; Back to Custom Fields
        </a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="px-4 py-3 rounded-md bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Admin Preview:</strong> This is what users will see when they are required to fill in the following fields before using the application. The form is non-functional in preview mode. This also pretends they are missing ALL the fields needed, so you can see the order.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($fields->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                            No active fields have "Force user to set" enabled. Enable it on a field to see it here.
                        </p>
                    @else
                        <div class="mb-6">
                            <p class="text-gray-700 dark:text-gray-300">
                                Before you continue, please fill in the following required information.
                            </p>
                        </div>

                        @foreach($fields as $field)
                            <div class="mb-6">
                                <x-input-label for="preview_{{ $field->id }}" :value="$field->name . ' *'" />

                                @if($field->field_type === 'text')
                                    <x-text-input
                                        id="preview_{{ $field->id }}"
                                        type="text"
                                        class="mt-1 block w-full"
                                        disabled />

                                @elseif($field->field_type === 'textarea')
                                    <x-textarea-input
                                        id="preview_{{ $field->id }}"
                                        rows="4"
                                        class="mt-1 block w-full"
                                        disabled></x-textarea-input>

                                @elseif($field->field_type === 'select')
                                    <x-select-input
                                        id="preview_{{ $field->id }}"
                                        class="mt-1 block w-full"
                                        disabled>
                                        <option value="">Select an option...</option>
                                        @foreach($field->options ?? [] as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </x-select-input>

                                @elseif($field->field_type === 'checkbox')
                                    <div class="mt-2 space-y-2">
                                        @foreach($field->options ?? [] as $option)
                                            <label class="flex items-center space-x-2">
                                                <input
                                                    type="checkbox"
                                                    disabled
                                                    class="rounded border-gray-300 dark:border-gray-700 text-brand-green shadow-sm dark:bg-gray-900">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($field->field_type === 'date')
                                    <x-text-input
                                        id="preview_{{ $field->id }}"
                                        type="date"
                                        class="mt-1 block w-full"
                                        disabled />

                                @elseif($field->field_type === 'number')
                                    <x-text-input
                                        id="preview_{{ $field->id }}"
                                        type="number"
                                        class="mt-1 block w-full"
                                        disabled />
                                @endif

                                @if($field->description)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $field->description }}</p>
                                @endif
                            </div>
                        @endforeach

                        <div class="flex justify-end">
                            <button type="button" disabled
                                class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest opacity-60 cursor-not-allowed">
                                Save &amp; Continue
                            </button>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

@props(['user', 'userEditableOnly' => false])

@php
    $query = \App\Models\CustomField::active()->ordered();
    
    if ($userEditableOnly) {
        $query->userEditable();
    }
    
    $customFields = $query->get();
@endphp

@if($customFields->isNotEmpty())
    @foreach($customFields as $field)
        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
            <dt class="form-label">
                {{ $field->name }}
                @if($field->is_required)
                    <span class="text-red-500">*</span>
                @endif
            </dt>
            <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                @php
                    $existingValue = $user->customFieldValues->where('custom_field_id', $field->id)->first();
                    $value = old('custom_field_' . $field->id, $existingValue->value ?? '');
                @endphp

                @if($field->field_type === 'text')
                    <x-text-input 
                        name="custom_field_{{ $field->id }}" 
                        id="custom_field_{{ $field->id }}" 
                        type="text" 
                        class="block w-full text-sm" 
                        :value="$value"
                        :required="$field->is_required" />
                
                @elseif($field->field_type === 'textarea')
                    <x-textarea-input 
                        name="custom_field_{{ $field->id }}" 
                        id="custom_field_{{ $field->id }}" 
                        rows="4"
                        class="block w-full text-sm"
                        :required="$field->is_required">{{ $value }}</x-textarea-input>
                
                @elseif($field->field_type === 'select')
                    <x-select-input 
                        name="custom_field_{{ $field->id }}" 
                        id="custom_field_{{ $field->id }}" 
                        class="block w-full text-sm"
                        :required="$field->is_required">
                        <option value="">Select an option...</option>
                        @foreach($field->options ?? [] as $option)
                            <option value="{{ $option }}" {{ $value == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </x-select-input>
                
                @elseif($field->field_type === 'checkbox')
                    <div class="space-y-2">
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
                                    class="rounded border-gray-300 dark:border-gray-700 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50 dark:bg-gray-900 dark:focus:ring-green-600">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                
                @elseif($field->field_type === 'date')
                    <x-text-input 
                        name="custom_field_{{ $field->id }}" 
                        id="custom_field_{{ $field->id }}" 
                        type="date" 
                        class="block w-full text-sm" 
                        :value="$value"
                        :required="$field->is_required" />
                
                @elseif($field->field_type === 'number')
                    <x-text-input 
                        name="custom_field_{{ $field->id }}" 
                        id="custom_field_{{ $field->id }}" 
                        type="number" 
                        class="block w-full text-sm" 
                        :value="$value"
                        :required="$field->is_required" />
                @endif

                @if($field->description)
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $field->description }}</p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('custom_field_' . $field->id)" />
            </dd>
        </div>
    @endforeach
@endif

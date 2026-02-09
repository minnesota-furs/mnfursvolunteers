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
        <div {{ $attributes->merge(['class' => 'mt-4']) }}>
            <x-input-label for="custom_field_{{ $field->id }}" :value="$field->name . ($field->is_required ? ' *' : '')" />
            
            @php
                $existingValue = $user->customFieldValues->where('custom_field_id', $field->id)->first();
                $value = old('custom_field_' . $field->id, $existingValue->value ?? '');
            @endphp

            @if($field->field_type === 'text')
                <x-text-input 
                    name="custom_field_{{ $field->id }}" 
                    id="custom_field_{{ $field->id }}" 
                    type="text" 
                    class="mt-1 block w-full" 
                    :value="$value"
                    :required="$field->is_required" />
            
            @elseif($field->field_type === 'textarea')
                <x-textarea-input 
                    name="custom_field_{{ $field->id }}" 
                    id="custom_field_{{ $field->id }}" 
                    rows="4"
                    class="mt-1 block w-full"
                    :required="$field->is_required">{{ $value }}</x-textarea-input>
            
            @elseif($field->field_type === 'select')
                <x-select-input 
                    name="custom_field_{{ $field->id }}" 
                    id="custom_field_{{ $field->id }}" 
                    class="mt-1 block w-full"
                    :required="$field->is_required">
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
                    :required="$field->is_required" />
            
            @elseif($field->field_type === 'number')
                <x-text-input 
                    name="custom_field_{{ $field->id }}" 
                    id="custom_field_{{ $field->id }}" 
                    type="number" 
                    class="mt-1 block w-full" 
                    :value="$value"
                    :required="$field->is_required" />
            @endif

            @if($field->description)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $field->description }}</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('custom_field_' . $field->id)" />
        </div>
    @endforeach
@endif

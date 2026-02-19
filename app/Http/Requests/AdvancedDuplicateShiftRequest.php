<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvancedDuplicateShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the controller via middleware/policies
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recurrence' => 'required|integer|min:1|max:100',
            'interval' => 'required|integer|min:1|max:365',
            'interval_unit' => 'required|in:hours,days,weeks',
            'naming_pattern' => 'required|in:sequence,start_time,prefix,suffix,prefix_sequence,suffix_sequence,custom,none',
            'custom_prefix' => 'nullable|string|max:100',
            'custom_suffix' => 'nullable|string|max:100',
            'copy_volunteers' => 'nullable|boolean',
            'maintain_capacity' => 'nullable|boolean',
            'copy_description' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'recurrence' => 'number of duplicates',
            'interval' => 'interval value',
            'interval_unit' => 'interval unit',
            'naming_pattern' => 'naming pattern',
            'custom_prefix' => 'prefix text',
            'custom_suffix' => 'suffix text',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'recurrence.max' => 'You cannot create more than 100 duplicates at once.',
            'interval.max' => 'The interval cannot exceed 365 :interval_unit.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvancedDuplicateEventRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'event_name' => 'required|string|max:255',
            'adjust_event_dates' => 'nullable|boolean',
            'event_date_offset_value' => 'nullable|integer',
            'event_date_offset_unit' => 'nullable|in:days,weeks,months,years',
            'copy_required_tags' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'event_name' => 'event name',
            'event_date_offset_value' => 'date offset value',
            'event_date_offset_unit' => 'date offset unit',
        ];
    }
}

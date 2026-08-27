<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftSeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'naming_pattern' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date'],
            'duration_hours' => ['required', 'integer', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'occurrences' => ['required', 'integer', 'min:1', 'max:100'],
            'gap_hours' => ['required', 'integer', 'min:0'],
            'gap_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'max_volunteers' => ['required', 'integer', 'min:1'],
            'double_hours' => ['nullable', 'boolean'],
            'shift_tags' => ['nullable', 'array'],
            'shift_tags.*' => ['integer', 'exists:tags,id'],
            'event_category_ids' => ['nullable', 'array'],
            'event_category_ids.*' => ['integer', Rule::exists('event_categories', 'id')->where('event_id', $this->route('event')->id)],
            'accessibility_conflicts' => ['array'],
            'accessibility_conflicts.*' => ['string', Rule::in(User::ACCESSIBILITY_NEEDS)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'accessibility_conflicts' => $this->input('accessibility_conflicts', []),
        ]);
    }
}

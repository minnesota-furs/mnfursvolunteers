<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateShiftRequest extends FormRequest
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
            'shift_ids' => ['required', 'array', 'min:1'],
            'shift_ids.*' => ['integer', 'exists:shifts,id'],
            'apply_max_volunteers' => ['nullable', 'boolean'],
            'max_volunteers' => ['nullable', 'required_if:apply_max_volunteers,1', 'integer', 'min:1'],
            'apply_description' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'apply_double_hours' => ['nullable', 'boolean'],
            'double_hours' => ['nullable', 'boolean'],
            'apply_accessibility_conflicts' => ['nullable', 'boolean'],
            'accessibility_conflicts' => ['array'],
            'accessibility_conflicts.*' => ['string', Rule::in(User::ACCESSIBILITY_NEEDS)],
            'add_category_ids' => ['array'],
            'add_category_ids.*' => ['integer', Rule::exists('event_categories', 'id')->where('event_id', $this->route('event')->id)],
            'remove_category_ids' => ['array'],
            'remove_category_ids.*' => ['integer', Rule::exists('event_categories', 'id')->where('event_id', $this->route('event')->id)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'accessibility_conflicts' => $this->input('accessibility_conflicts', []),
            'add_category_ids' => $this->input('add_category_ids', []),
            'remove_category_ids' => $this->input('remove_category_ids', []),
        ]);
    }
}

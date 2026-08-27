<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'max_volunteers' => ['required', 'integer', 'min:1'],
            'double_hours' => ['nullable', 'boolean'],
            'user_id' => ['nullable', 'array'],
            'user_id.*' => ['integer', 'exists:users,id'],
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

<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AccessibilityNeedsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'has_accessibility_needs' => ['required', 'boolean'],
            'accessibility_needs' => ['array'],
            'accessibility_needs.*' => ['string', Rule::in(User::ACCESSIBILITY_NEEDS)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('has_accessibility_needs') && empty($this->input('accessibility_needs', []))) {
                $validator->errors()->add(
                    'accessibility_needs',
                    'Select at least one accessibility need or choose No Accessibility Needs.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if (! $this->boolean('has_accessibility_needs')) {
            $this->merge(['accessibility_needs' => []]);
        }
    }
}

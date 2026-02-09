<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'pronouns' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];

        // Add validation rules for user-editable custom fields
        $customFields = \App\Models\CustomField::active()->userEditable()->get();
        foreach ($customFields as $field) {
            $fieldKey = 'custom_field_' . $field->id;
            
            // Build validation rules based on field type and requirements
            $fieldRules = [];
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            switch ($field->field_type) {
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:65535';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'select':
                    if (!empty($field->options)) {
                        $fieldRules[] = Rule::in($field->options);
                    }
                    break;
                case 'checkbox':
                    $fieldRules[] = 'array';
                    if (!empty($field->options)) {
                        $rules[$fieldKey . '.*'] = Rule::in($field->options);
                    }
                    break;
            }
            
            $rules[$fieldKey] = $fieldRules;
        }

        return $rules;
    }
}

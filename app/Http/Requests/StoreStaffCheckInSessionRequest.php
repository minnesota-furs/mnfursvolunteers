<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffCheckInSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'scope' => ['required', Rule::in(['sector', 'department'])],
            'sector_id' => ['nullable', 'required_if:scope,sector', Rule::exists('sectors', 'id')],
            'department_id' => ['nullable', 'required_if:scope,department', Rule::exists('departments', 'id')],
            'checklist_items' => ['nullable', 'array', 'max:12'],
            'checklist_items.*' => ['nullable', 'string', 'max:60', 'distinct'],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*' => [
                'integer',
                'distinct',
                Rule::exists('custom_fields', 'id')->where('is_active', true),
            ],
            'collect_signature' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sector_id.required_if' => 'Please select a sector.',
            'department_id.required_if' => 'Please select a department.',
            'checklist_items.max' => 'A session can have at most 12 checklist items.',
            'checklist_items.*.distinct' => 'Each checklist item must have a unique label.',
            'custom_fields.*.exists' => 'One of the selected custom fields is unavailable.',
        ];
    }
}

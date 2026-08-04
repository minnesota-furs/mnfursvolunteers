<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffCheckInReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scope' => ['nullable', Rule::in(['sector', 'department'])],
            'sector_id' => ['nullable', 'required_if:scope,sector', 'integer', Rule::exists('sectors', 'id')],
            'department_id' => ['nullable', 'required_if:scope,department', 'integer', Rule::exists('departments', 'id')],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*' => [
                'integer',
                'distinct',
                Rule::exists('custom_fields', 'id')->where('is_active', true),
            ],
            'checklist_items' => ['nullable', 'array', 'max:12'],
            'checklist_items.*' => ['nullable', 'string', 'max:60', 'distinct'],
            'include_signature' => ['nullable', 'boolean'],
            'group_alphabetically' => ['nullable', 'boolean'],
            'alphabetical_by' => ['nullable', Rule::in(['name', 'first_name', 'last_name'])],
            'list_legal_name' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sector_id.required_if' => 'Please select a sector.',
            'department_id.required_if' => 'Please select a department.',
            'custom_fields.*.exists' => 'One of the selected custom fields is unavailable.',
            'checklist_items.max' => 'A check-in report can have at most 12 checklist items.',
            'checklist_items.*.distinct' => 'Each checklist item must have a unique label.',
            'alphabetical_by.in' => 'Please select a valid alphabetical grouping.',
        ];
    }
}

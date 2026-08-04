<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomFieldReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'custom_field_id' => [
                'nullable',
                'integer',
                Rule::exists('custom_fields', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'mode' => ['nullable', Rule::in(['count', 'people'])],
            'search' => ['nullable', 'string', 'max:255'],
            'response' => ['nullable', 'string', 'max:255'],
            'sector_id' => ['nullable', 'integer', Rule::exists('sectors', 'id')],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['name', 'department_count'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'months' => ['nullable', 'integer', Rule::in([6, 12, 24])],
            'sector_id' => ['nullable', 'integer', Rule::exists('sectors', 'id')],
        ];
    }
}

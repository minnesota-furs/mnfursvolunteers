<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = $this->route('department');

        return $department instanceof Department
            && $this->user()?->can('manage', $department) === true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'visibility' => 'required|in:internal,draft',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter a name for the staffing roster.',
            'start_date.required' => 'Choose when coverage begins.',
            'end_date.required' => 'Choose when coverage ends.',
            'end_date.after_or_equal' => 'Coverage must end after it begins.',
        ];
    }
}

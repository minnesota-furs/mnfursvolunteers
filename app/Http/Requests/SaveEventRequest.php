<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'faq' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'signup_open_date' => 'nullable|date|before_or_equal:end_date',
            'location' => 'nullable|string',
            'visibility' => 'required|in:public,unlisted,internal,draft',
            'hide_past_shifts' => 'nullable|boolean',
            'auto_credit_hours' => 'nullable|boolean',
            'require_eligibility' => 'nullable|boolean',
            'created_by' => 'nullable|exists:users,id',
            'required_tags' => 'nullable|array',
            'required_tags.*' => 'exists:tags,id',
            'required_departments' => 'nullable|array',
            'required_departments.*' => 'exists:departments,id',
            'required_sectors' => 'nullable|array',
            'required_sectors.*' => 'exists:sectors,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter an event name.',
            'start_date.required' => 'Choose when the event starts.',
            'end_date.required' => 'Choose when the event ends.',
            'end_date.after_or_equal' => 'The event must end after it starts.',
            'visibility.required' => 'Choose who can see this event.',
            'required_tags.*.exists' => 'One of the selected tags no longer exists.',
            'required_departments.*.exists' => 'One of the selected departments no longer exists.',
            'required_sectors.*.exists' => 'One of the selected sectors no longer exists.',
        ];
    }
}

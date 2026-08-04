<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-announcements') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'expires_at' => ['nullable', 'date'],
            'volunteers_only' => ['nullable', 'boolean'],
            'departments' => ['nullable', 'array', Rule::prohibitedIf($this->boolean('volunteers_only'))],
            'departments.*' => ['integer', 'exists:departments,id'],
            'sectors' => ['nullable', 'array', Rule::prohibitedIf($this->boolean('volunteers_only'))],
            'sectors.*' => ['integer', 'exists:sectors,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'departments.*.exists' => 'One of the selected departments no longer exists.',
            'sectors.*.exists' => 'One of the selected sectors no longer exists.',
        ];
    }
}

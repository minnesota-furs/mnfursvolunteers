<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveElectionMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-elections') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Give the milestone a name, like "Candidate Bios Due".',
            'starts_at.required' => 'Choose a date and time for the milestone.',
            'starts_at.date' => 'The milestone date and time is not valid.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteStaffCheckInRequest extends FormRequest
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
            'completed_items' => ['nullable', 'array'],
            'completed_items.*' => ['string', 'max:60', 'distinct'],
            'signature_data' => ['nullable', 'string', 'max:2000000', 'starts_with:data:image/png;base64,'],
        ];
    }

    public function messages(): array
    {
        return [
            'signature_data.starts_with' => 'The captured signature is invalid.',
            'signature_data.max' => 'The captured signature is too large.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandPaletteSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if (! app_setting('require_department_for_user_index', false)) {
            return true;
        }

        return $user->isAdmin()
            || $user->hasPermission('manage-users')
            || $user->departments()->exists();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'query.min' => 'Enter at least two characters to search for users.',
        ];
    }
}

<?php

namespace App\Http\Requests\Auth;

use App\Models\ApplicationSetting;
use App\Models\User;
use App\Rules\NotBlacklisted;
use App\Support\WarningEmailDomains;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $inviteCodeRequired = ApplicationSetting::get('require_invite_code', false);
        $onboardingAgreement = ApplicationSetting::get('onboarding_agreement');
        $warningEmailDomains = ApplicationSetting::get('warning_email_domains', '');
        $warningEmailAcknowledgementRequired = WarningEmailDomains::includesEmail($warningEmailDomains, $this->email);

        return [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255', new NotBlacklisted('name', $this->first_name, $this->last_name)],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new NotBlacklisted('email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'invite_code' => [$inviteCodeRequired ? 'required' : 'nullable', 'string', 'max:32'],
            'agree_to_terms' => [$onboardingAgreement ? 'accepted' : 'nullable'],
            'warning_email_acknowledged' => [
                Rule::requiredIf($warningEmailAcknowledgementRequired),
                $warningEmailAcknowledgementRequired ? 'accepted' : 'nullable',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'warning_email_acknowledged.required' => 'Please confirm that you want to register with this organization email address.',
            'warning_email_acknowledged.accepted' => 'Please confirm that you want to register with this organization email address.',
        ];
    }
}

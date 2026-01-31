<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotBlacklisted implements ValidationRule
{
    protected string $type;
    protected ?string $firstName;
    protected ?string $lastName;

    public function __construct(string $type, ?string $firstName = null, ?string $lastName = null)
    {
        $this->type = $type; // 'email' or 'name'
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->type === 'email') {
            $this->validateEmail($value, $fail);
        } elseif ($this->type === 'name') {
            $this->validateName($value, $fail);
        }
    }

    /**
     * Validate email against blacklist.
     */
    protected function validateEmail(string $email, Closure $fail): void
    {
        $blacklist = $this->getEmailBlacklist();

        if (empty($blacklist)) {
            return;
        }

        $normalizedEmail = strtolower(trim($email));

        foreach ($blacklist as $blacklistedEmail) {
            $normalizedBlacklist = strtolower(trim($blacklistedEmail));
            
            if ($normalizedEmail === $normalizedBlacklist) {
                $fail('This email is not allowed. Please contact a staff administrator if you believe this is an error.');
                return;
            }
        }
    }

    /**
     * Validate full name (first + last) against blacklist.
     */
    protected function validateName(string $currentValue, Closure $fail): void
    {
        // For name validation, we need both first and last names
        if (!$this->firstName || !$this->lastName) {
            return;
        }

        $blacklist = $this->getNameBlacklist();

        if (empty($blacklist)) {
            return;
        }

        $fullName = trim($this->firstName) . ' ' . trim($this->lastName);
        $normalizedFullName = strtolower($fullName);

        foreach ($blacklist as $blacklistedName) {
            $normalizedBlacklist = strtolower(trim($blacklistedName));
            
            if ($normalizedFullName === $normalizedBlacklist) {
                $fail('This name combination is not allowed. Please contact a staff administrator if you believe this is an error.');
                return;
            }
        }
    }

    /**
     * Get the email blacklist.
     */
    protected function getEmailBlacklist(): array
    {
        $blacklistString = app_setting('blacklist_emails', '');
        
        if (empty($blacklistString)) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(',', $blacklistString)),
            fn($item) => !empty($item)
        );
    }

    /**
     * Get the name blacklist.
     */
    protected function getNameBlacklist(): array
    {
        $blacklistString = app_setting('blacklist_names', '');
        
        if (empty($blacklistString)) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(',', $blacklistString)),
            fn($item) => !empty($item)
        );
    }
}

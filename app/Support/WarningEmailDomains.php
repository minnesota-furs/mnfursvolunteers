<?php

namespace App\Support;

class WarningEmailDomains
{
    /**
     * @return array<int, string>
     */
    public static function parse(?string $domains): array
    {
        if (blank($domains)) {
            return [];
        }

        return collect(preg_split('/[\s,]+/', $domains) ?: [])
            ->map(fn (string $domain): string => strtolower(ltrim(trim($domain), '@')))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function includesEmail(?string $domains, ?string $email): bool
    {
        if (blank($email) || ! str_contains($email, '@')) {
            return false;
        }

        $emailDomain = strtolower((string) str($email)->afterLast('@'));

        return in_array($emailDomain, self::parse($domains), true);
    }
}

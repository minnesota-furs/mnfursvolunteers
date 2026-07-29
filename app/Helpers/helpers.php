<?php

if (!function_exists('format_hours')) {
    /**
     * Format hours, hide decimals if even
     *
     * @param  float  $hours
     * @return string
     */
    function format_hours($hours)
    {
        // If the hours are a whole number, show without decimals
        if (floor($hours) == $hours) {
            return number_format($hours, 0);
        }

        // Otherwise, show with 2 decimals
        return number_format($hours, 2);
    }
}

if (!function_exists('app_setting')) {
    /**
     * Get an application setting value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function app_setting(string $key, $default = null)
    {
        return \App\Models\ApplicationSetting::get($key, $default);
    }
}

if (!function_exists('app_name')) {
    /**
     * Get the application name.
     *
     * @return string
     */
    function app_name()
    {
        return app_setting('app_name', config('app.name', 'MNFursVolunteers'));
    }
}

if (!function_exists('app_logo')) {
    /**
     * Get the application logo URL.
     *
     * @return string
     */
    function app_logo()
    {
        return \App\Models\ApplicationSetting::getLogo();
    }
}

if (!function_exists('app_favicon')) {
    /**
     * Get the application favicon URL.
     *
     * @return string
     */
    function app_favicon()
    {
        return \App\Models\ApplicationSetting::getFavicon();
    }
}

if (!function_exists('user_display_name')) {
    /**
     * Get the display name for a user based on the application setting.
     *
     * Uses the 'user_display_name' setting: 'alias' returns the name field,
     * 'legal_name' returns first + last name.
     *
     * @param  \App\Models\User  $user
     * @return string
     */
    function user_display_name(\App\Models\User $user): string
    {
        return $user->displayName();
    }
}

if (!function_exists('app_timezone')) {
    /**
     * Get the effective application timezone (admin override, falling back to config/app.php).
     *
     * @return string
     */
    function app_timezone(): string
    {
        return app_setting('app_timezone') ?: config('app.timezone', 'UTC');
    }
}

if (!function_exists('user_timezone')) {
    /**
     * Get the effective timezone to display dates/times in for a user
     * (defaults to the currently authenticated user). Falls back to the
     * application timezone for guests or users without a saved preference.
     *
     * @param  \App\Models\User|null  $user
     * @return string
     */
    function user_timezone(?\App\Models\User $user = null): string
    {
        $user ??= auth()->user();

        return $user ? $user->effectiveTimezone() : app_timezone();
    }
}

if (!function_exists('grouped_timezones')) {
    /**
     * All PHP timezone identifiers, grouped by region (e.g. "America"), for
     * populating timezone <select> inputs.
     *
     * @return \Illuminate\Support\Collection
     */
    function grouped_timezones(): \Illuminate\Support\Collection
    {
        return collect(\DateTimeZone::listIdentifiers(\DateTimeZone::ALL))
            ->groupBy(fn ($tz) => str_contains($tz, '/') ? explode('/', $tz, 2)[0] : 'Other');
    }
}

if (!function_exists('common_timezones')) {
    /**
     * A short list of commonly-used US timezones, for pinning to the top of
     * timezone <select> inputs above the full alphabetical region list.
     *
     * @return array<string>
     */
    function common_timezones(): array
    {
        return [
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Phoenix',
            'America/Los_Angeles',
            'America/Anchorage',
            'Pacific/Honolulu',
            'UTC',
        ];
    }
}

if (!function_exists('feature_enabled')) {
    /**
     * Check if a feature is enabled.
     *
     * @param  string  $feature
     * @return bool
     */
    function feature_enabled(string $feature)
    {
        return (bool) app_setting("feature_{$feature}", true);
    }
}

if (!function_exists('feature_is_beta')) {
    /**
     * Check if a feature is in beta.
     *
     * @param  string  $feature
     * @return bool
     */
    function feature_is_beta(string $feature)
    {
        return app(\App\Services\FeatureService::class)->isBeta($feature);
    }
}

if (!function_exists('hosting_info')) {
    /**
     * Get managed hosting paid-through information, if configured.
     *
     * @return array{date: \Illuminate\Support\Carbon, days_remaining: int, status: string}|null
     */
    function hosting_info(): ?array
    {
        $paidThrough = config('app.hosting_paid_through');

        if (empty($paidThrough)) {
            return null;
        }

        try {
            $date = \Illuminate\Support\Carbon::parse($paidThrough)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        $today = \Illuminate\Support\Carbon::today();
        $daysRemaining = $today->diffInDays($date, false);

        return [
            'date' => $date,
            'days_remaining' => $daysRemaining,
            'status' => match (true) {
                $daysRemaining < 0 => 'expired',
                $daysRemaining <= 14 => 'expiring_soon',
                default => 'active',
            },
        ];
    }
}

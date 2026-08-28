<?php

use App\Models\ApplicationSetting;
use App\Models\User;
use App\Services\FeatureService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

if (! function_exists('format_hours')) {
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

if (! function_exists('app_setting')) {
    /**
     * Get an application setting value.
     *
     * @param  mixed  $default
     * @return mixed
     */
    function app_setting(string $key, $default = null)
    {
        return ApplicationSetting::get($key, $default);
    }
}

if (! function_exists('app_name')) {
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

if (! function_exists('app_logo')) {
    /**
     * Get the application logo URL.
     *
     * @return string
     */
    function app_logo()
    {
        return ApplicationSetting::getLogo();
    }
}

if (! function_exists('app_favicon')) {
    /**
     * Get the application favicon URL.
     *
     * @return string
     */
    function app_favicon()
    {
        return ApplicationSetting::getFavicon();
    }
}

if (! function_exists('user_display_name')) {
    /**
     * Get the display name for a user based on the application setting.
     *
     * Uses the 'user_display_name' setting: 'alias' returns the name field,
     * 'legal_name' returns first + last name.
     */
    function user_display_name(User $user): string
    {
        return $user->displayName();
    }
}

if (! function_exists('app_timezone')) {
    /**
     * Get the effective application timezone (admin override, falling back to config/app.php).
     */
    function app_timezone(): string
    {
        return app_setting('app_timezone') ?: config('app.timezone', 'UTC');
    }
}

if (! function_exists('user_timezone')) {
    /**
     * Get the effective timezone to display dates/times in for a user
     * (defaults to the currently authenticated user). Falls back to the
     * application timezone for guests or users without a saved preference.
     */
    function user_timezone(?User $user = null): string
    {
        $user ??= auth()->user();

        return $user ? $user->effectiveTimezone() : app_timezone();
    }
}

if (! function_exists('grouped_timezones')) {
    /**
     * All PHP timezone identifiers, grouped by region (e.g. "America"), for
     * populating timezone <select> inputs.
     */
    function grouped_timezones(): Collection
    {
        return collect(DateTimeZone::listIdentifiers(DateTimeZone::ALL))
            ->groupBy(fn ($tz) => str_contains($tz, '/') ? explode('/', $tz, 2)[0] : 'Other');
    }
}

if (! function_exists('common_timezones')) {
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

if (! function_exists('concat_configured')) {
    /**
     * Check whether the ConCat integration has connected credentials.
     *
     * @return bool
     */
    function concat_configured()
    {
        return (bool) app_setting('concat_client_id');
    }
}

if (! function_exists('feature_enabled')) {
    /**
     * Check if a feature is enabled.
     *
     * @return bool
     */
    function feature_enabled(string $feature)
    {
        return (bool) app_setting("feature_{$feature}", true);
    }
}

if (! function_exists('feature_is_beta')) {
    /**
     * Check if a feature is in beta.
     *
     * @return bool
     */
    function feature_is_beta(string $feature)
    {
        return app(FeatureService::class)->isBeta($feature);
    }
}

if (! function_exists('hosting_info')) {
    /**
     * Get managed hosting paid-through information, if configured.
     *
     * @return array{date: Carbon, days_remaining: int, status: string}|null
     */
    function hosting_info(): ?array
    {
        $paidThrough = config('app.hosting_paid_through');

        if (empty($paidThrough)) {
            return null;
        }

        try {
            $date = Carbon::parse($paidThrough)->startOfDay();
        } catch (Throwable) {
            return null;
        }

        $today = Carbon::today();
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

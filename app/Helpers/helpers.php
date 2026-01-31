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

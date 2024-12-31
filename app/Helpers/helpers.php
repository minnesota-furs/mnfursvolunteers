<?php

use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

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
    
    if (!function_exists('settings')) {
        /**
         * Get or set application settings.
         *
         * @param string|null $key
         * @param mixed|null $default
         * @return mixed
         */
        function settings($key = null, $default = null)
        {
            // Retrieve all settings from the cache or database
            $allSettings = Cache::rememberForever('app_settings', function () {
                return Setting::pluck('value', 'key')->toArray();
            });
    
            // If no key is provided, return all settings
            if (is_null($key)) {
                return $allSettings;
            }
    
            // Return the specific setting or the default value
            return $allSettings[$key] ?? $default;
        }
    
        /**
         * Get settings by group.
         *
         * @param string $group
         * @return array
         */
        function settings_by_group($group)
        {
            return Cache::rememberForever("app_settings_group_{$group}", function () use ($group) {
                return Setting::where('group', $group)->pluck('value', 'key')->toArray();
            });
        }
    }
}

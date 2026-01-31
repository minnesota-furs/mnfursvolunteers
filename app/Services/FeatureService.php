<?php

namespace App\Services;

use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    /**
     * Check if a feature is enabled.
     *
     * @param  string  $feature
     * @return bool
     */
    public function isEnabled(string $feature): bool
    {
        return feature_enabled($feature);
    }

    /**
     * Check if a feature is disabled.
     *
     * @param  string  $feature
     * @return bool
     */
    public function isDisabled(string $feature): bool
    {
        return !$this->isEnabled($feature);
    }

    /**
     * Enable a feature.
     *
     * @param  string  $feature
     * @return void
     */
    public function enable(string $feature): void
    {
        ApplicationSetting::set(
            "feature_{$feature}",
            true,
            'boolean',
            "Enable/disable the {$feature} feature",
            'feature_flags'
        );
    }

    /**
     * Disable a feature.
     *
     * @param  string  $feature
     * @return void
     */
    public function disable(string $feature): void
    {
        ApplicationSetting::set(
            "feature_{$feature}",
            false,
            'boolean',
            "Enable/disable the {$feature} feature",
            'feature_flags'
        );
    }

    /**
     * Toggle a feature on/off.
     *
     * @param  string  $feature
     * @return bool The new state
     */
    public function toggle(string $feature): bool
    {
        $newState = !$this->isEnabled($feature);
        
        ApplicationSetting::set(
            "feature_{$feature}",
            $newState,
            'boolean',
            "Enable/disable the {$feature} feature",
            'feature_flags'
        );

        return $newState;
    }

    /**
     * Get all feature flags with their current states.
     *
     * @return array
     */
    public function all(): array
    {
        $settings = ApplicationSetting::where('group', 'feature_flags')->get();
        $config = config('appSettings.feature_flags', []);
        
        return $settings->mapWithKeys(function ($setting) use ($config) {
            $featureName = str_replace('feature_', '', $setting->key);
            $configKey = "feature_{$featureName}";
            
            return [$featureName => [
                'enabled' => (bool) $setting->value,
                'description' => $setting->description,
                'beta' => $config[$configKey]['beta'] ?? false,
            ]];
        })->toArray();
    }

    /**
     * Check if any of the given features are enabled.
     *
     * @param  array  $features
     * @return bool
     */
    public function anyEnabled(array $features): bool
    {
        foreach ($features as $feature) {
            if ($this->isEnabled($feature)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if all of the given features are enabled.
     *
     * @param  array  $features
     * @return bool
     */
    public function allEnabled(array $features): bool
    {
        foreach ($features as $feature) {
            if (!$this->isEnabled($feature)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a feature is in beta.
     *
     * @param  string  $feature
     * @return bool
     */
    public function isBeta(string $feature): bool
    {
        $config = config('appSettings.feature_flags', []);
        $key = "feature_{$feature}";
        
        return $config[$key]['beta'] ?? false;
    }
}

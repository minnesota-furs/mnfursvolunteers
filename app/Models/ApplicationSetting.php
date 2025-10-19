<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ApplicationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null, string $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'description' => $description,
                'group' => $group,
            ]
        );

        Cache::forget("app_setting_{$key}");
        
        return $setting;
    }

    /**
     * Cast value to appropriate type.
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            'file' => $value, // Returns path
            default => $value,
        };
    }

    /**
     * Get all settings grouped by group.
     */
    public static function getAllGrouped()
    {
        return self::all()->groupBy('group')->map(function ($settings) {
            return $settings->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => self::castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'description' => $setting->description,
                ]];
            });
        });
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache()
    {
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("app_setting_{$key}");
        }
    }

    /**
     * Get logo URL.
     */
    public static function getLogo()
    {
        $logo = self::get('app_logo');
        
        if ($logo && Storage::disk('public')->exists($logo)) {
            return Storage::url($logo);
        }
        
        return asset('images/logo.png'); // Fallback to default
    }

    /**
     * Get favicon URL.
     */
    public static function getFavicon()
    {
        $favicon = self::get('app_favicon');
        
        if ($favicon && Storage::disk('public')->exists($favicon)) {
            return Storage::url($favicon);
        }
        
        return asset('favicon.ico'); // Fallback to default
    }
}

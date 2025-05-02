<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $site_name = 'VolunteerApp';
    
    public bool $site_active = true;

    public static function group(): string
    {
        return 'general';
    }
}
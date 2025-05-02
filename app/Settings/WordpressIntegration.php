<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WordpressIntegration extends Settings
{
    public bool $wp_enabled = false;

    public static function group(): string
    {
        return 'wordpress';
    }
}
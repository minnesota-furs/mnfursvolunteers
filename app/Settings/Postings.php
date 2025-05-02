<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class Postings extends Settings
{   
    public bool $postings_active = false;

    public bool $inqury_active = false;

    public static function group(): string
    {
        return 'postings';
    }
}
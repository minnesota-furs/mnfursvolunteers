<?php

namespace App\FakerProviders;

use Faker\Provider\Base as BaseProvider;

class DepartmentProvider extends BaseProvider
{
    protected static $departments = [
        'Events',
        'Programming',
        'Staff Admin',
        'Marketing',
        'Communications',
        'Development',
        'Finance',
        'Web Development',
        'Main Stage',
        'Administration',
        'Consuite',
        'Registration',
        'Volunteers',
        'Security',
        'Operations',
        'Guest Relations',
        'Tech',
        'Art Show',
        'Dealers Room',
        'Gaming',
        'Hospitality',
        'Logistics',
        'Programming',
        'Publications',
        'Safety',
        'Signage',
        'Site Selection',
        'Sponsorship',
        'Treasury',
        'Video',
        'Web',
        'Writing',
        'Youth',
        'Accessibility',
        'Auction',
        'Charity',
        'Costuming'
    ];

    public static function departmentName()
    {
        return static::randomElement(static::$departments);
    }
}

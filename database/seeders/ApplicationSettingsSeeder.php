<?php

namespace Database\Seeders;

use App\Models\ApplicationSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Branding
            [
                'key' => 'app_name',
                'value' => 'MNFursVolunteers',
                'type' => 'string',
                'description' => 'Application name displayed throughout the site',
                'group' => 'branding',
            ],
            [
                'key' => 'app_tagline',
                'value' => 'Managing volunteers with ease',
                'type' => 'string',
                'description' => 'Short tagline or slogan',
                'group' => 'branding',
            ],
            [
                'key' => 'app_description',
                'value' => 'A comprehensive volunteer management system for tracking hours, managing events, and organizing your volunteer community.',
                'type' => 'string',
                'description' => 'Brief description of the organization',
                'group' => 'branding',
            ],
            [
                'key' => 'primary_color',
                'value' => '#10b981',
                'type' => 'string',
                'description' => 'Primary brand color (hex)',
                'group' => 'branding',
            ],
            [
                'key' => 'secondary_color',
                'value' => '#3b82f6',
                'type' => 'string',
                'description' => 'Secondary brand color (hex)',
                'group' => 'branding',
            ],

            // Features
            [
                'key' => 'feature_elections',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable/disable elections module',
                'group' => 'features',
            ],
            [
                'key' => 'feature_job_listings',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable/disable job listings module',
                'group' => 'features',
            ],
            [
                'key' => 'feature_one_off_events',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable/disable one-off events module',
                'group' => 'features',
            ],
            [
                'key' => 'feature_volunteer_events',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable/disable volunteer events with shifts',
                'group' => 'features',
            ],

            // Contact
            [
                'key' => 'contact_email',
                'value' => 'contact@example.com',
                'type' => 'string',
                'description' => 'Contact email address',
                'group' => 'contact',
            ],
            [
                'key' => 'contact_phone',
                'value' => '',
                'type' => 'string',
                'description' => 'Contact phone number',
                'group' => 'contact',
            ],
        ];

        foreach ($settings as $setting) {
            ApplicationSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

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
            [
                'key' => 'footer_text',
                'value' => '© 2001-2025 Minnesota Furs a 501c3 Minnesota Non-Profit • Built and maintained by local furs.',
                'type' => 'string',
                'description' => 'Footer text displayed at the bottom of all pages',
                'group' => 'branding',
            ],
            [
                'key' => 'social_facebook_url',
                'value' => 'https://www.facebook.com/pages/MNFurs/252250438145155',
                'type' => 'string',
                'description' => 'Facebook page URL. Leave empty to hide the icon.',
                'group' => 'branding',
            ],
            [
                'key' => 'social_twitter_url',
                'value' => 'https://www.twitter.com/MNFurs',
                'type' => 'string',
                'description' => 'X/Twitter profile URL. Leave empty to hide the icon.',
                'group' => 'branding',
            ],
            [
                'key' => 'social_github_url',
                'value' => 'https://github.com/orgs/minnesota-furs/',
                'type' => 'string',
                'description' => 'GitHub organization URL. Leave empty to hide the icon.',
                'group' => 'branding',
            ],
            [
                'key' => 'social_youtube_url',
                'value' => 'https://www.youtube.com/@MNFurs',
                'type' => 'string',
                'description' => 'YouTube channel URL. Leave empty to hide the icon.',
                'group' => 'branding',
            ],
            [
                'key' => 'social_website_url',
                'value' => '',
                'type' => 'string',
                'description' => 'Organization website URL. Leave empty to hide the icon.',
                'group' => 'branding',
            ],
            [
                'key' => 'social_twitch_url',
                'value' => '',
                'type' => 'string',
                'description' => 'Twitch channel URL. Leave empty to hide the icon.',
                'group' => 'branding',
            ],
            [
                'key' => 'social_discord_url',
                'value' => '',
                'type' => 'string',
                'description' => 'Discord server invite URL. Leave empty to hide the icon.',
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
            // Event Settings
            [
                'key' => 'checkin_hours_before',
                'value' => '1',
                'type' => 'number',
                'description' => 'How many hours before an event starts can users check in',
                'group' => 'event_settings',
            ],
            [
                'key' => 'checkin_hours_after',
                'value' => '12',
                'type' => 'number',
                'description' => 'How many hours after an event ends can users check in',
                'group' => 'event_settings',
            ],
            [
                'key' => 'contact_phone',
                'value' => '',
                'type' => 'string',
                'description' => 'Contact phone number',
                'group' => 'contact',
            ],

            // Feature Flags
            [
                'key' => 'feature_recognition',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable/disable recognition and awards module',
                'group' => 'features',
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

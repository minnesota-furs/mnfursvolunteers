<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        $settings = config('appSettings'); // Load settings from config file

        foreach ($settings as $group => $groupSettings) {
            foreach ($groupSettings as $key => $details) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $details['value'],
                        'type' => $details['type'],
                        'group' => $details['group'],
                        'label' => $details['label'] ?? null,
                        'description' => $details['description'] ?? null,
                    ]
                );
            }
        }
    }
}

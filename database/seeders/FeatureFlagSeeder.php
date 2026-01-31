<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationSetting;

class FeatureFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = config('appSettings.feature_flags', []);

        foreach ($features as $key => $settings) {
            ApplicationSetting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $settings['value'],
                    'type' => $settings['type'],
                    'description' => $settings['description'],
                    'group' => $settings['group'],
                ]
            );
        }

        $this->command->info('Feature flags seeded successfully!');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Sectors ---
        $mnfurs = Sector::factory()->create(['name' => 'MNFurs']);
        $fm     = Sector::factory()->create(['name' => 'Furry Migration']);
        $frolic = Sector::factory()->create(['name' => 'Frolic']);

        // --- Departments ---
        foreach (['Administration', 'Communications', 'Events', 'Finance', 'Marketing', 'Web & Technology'] as $name) {
            Department::factory()->create(['name' => $name, 'sector_id' => $mnfurs->id]);
        }

        foreach ([
            'Art Show', 'Consuite', 'Dealers Room', 'Gaming', 'Guest Relations',
            'Hospitality', 'Logistics', 'Main Stage', 'Operations', 'Programming',
            'Registration', 'Security', 'Tech', 'Volunteers',
        ] as $name) {
            Department::factory()->create(['name' => $name, 'sector_id' => $fm->id]);
        }

        foreach (['Operations', 'Programming', 'Registration', 'Volunteers'] as $name) {
            Department::factory()->create(['name' => $name, 'sector_id' => $frolic->id]);
        }

        // --- Users ---
        User::factory()->create([
            'name'  => 'Regular User',
            'email' => 'user@mnfurs.org',
            'password' => bcrypt('password'),
        ]);

        User::factory()->admin()->create([
            'name'  => 'Admin User',
            'email' => 'admin@mnfurs.org',
            'password' => bcrypt('password'),
        ]);

        // 12 plain users (no department assignments)
        User::factory()->count(12)->create();

        // 6 users each attached to 1–2 departments
        User::factory()->count(4)->withDepartments(1)->create();
        User::factory()->count(2)->withDepartments(2)->create();

        // --- Fiscal Ledgers ---
        FiscalLedger::factory()->forYear(2025)->create();
        FiscalLedger::factory()->forYear(2026)->create();

        // --- Application settings & feature flags ---
        $this->call([
            ApplicationSettingsSeeder::class,
            FeatureFlagSeeder::class,
            MusicConSeeder::class,
        ]);
    }
}

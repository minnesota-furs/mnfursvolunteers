<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory(3)->count(5)->create();

        $sector_mnf = \App\Models\Sector::factory()->create(['name' => 'MNFurs']);
        $sector_fm  = \App\Models\Sector::factory()->create(['name' => 'Furry Migration']);
        $sector_frl = \App\Models\Sector::factory()->create(['name' => 'Frolic']);

        \App\Models\Department::factory(6)->create(['sector_id' => $sector_mnf->id]);
        \App\Models\Department::factory(14)->create(['sector_id' => $sector_fm->id]);
        \App\Models\Department::factory(4)->create(['sector_id' => $sector_frl->id]);

        \App\Models\User::factory()->create([
            'name' => 'User',
            'email' => 'user@mnfurs.org',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@mnfurs.org',
            'admin' => true
        ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

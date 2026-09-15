<?php

use App\Models\Sector;

it('returns sectors ordered by name', function () {
    Sector::factory()->create(['name' => 'Zebra Zone']);
    Sector::factory()->create(['name' => 'Furry Migration']);

    $response = $this->getJson('/api/sectors');

    $response->assertSuccessful()
        ->assertJson(['status' => 'success'])
        ->assertJsonPath('data.0.name', 'Furry Migration')
        ->assertJsonPath('data.1.name', 'Zebra Zone');
});

it('does not require authentication', function () {
    $response = $this->getJson('/api/sectors');

    $response->assertSuccessful();
});

<?php

use App\Models\ApplicationSetting;
use App\Models\User;

it('requires authentication to search for command palette users', function () {
    $this->getJson(route('command-palette.search', ['query' => 'fox']))
        ->assertUnauthorized();
});

it('searches active users by all supported identity fields', function (string $query) {
    $viewer = User::factory()->create(['onboarded_at' => now()]);
    $matchingUser = User::factory()->create([
        'name' => 'Star Fox',
        'first_name' => 'Robin',
        'last_name' => 'McCloud',
        'email' => 'star.fox@example.com',
        'vol_code' => 'FOX123',
        'active' => true,
    ]);

    $response = $this->actingAs($viewer)
        ->getJson(route('command-palette.search', ['query' => $query]));

    $response
        ->assertSuccessful()
        ->assertJsonPath('users.0.id', $matchingUser->id)
        ->assertJsonPath('users.0.alias', 'Star Fox')
        ->assertJsonPath('users.0.email', 'star.fox@example.com')
        ->assertJsonPath('users.0.vol_code', 'FOX123')
        ->assertJsonMissing(['password' => $matchingUser->password]);
})->with([
    'volunteer code' => 'FOX123',
    'email' => 'star.fox@',
    'alias' => 'Star Fox',
    'legal name' => 'Robin McCloud',
]);

it('does not return inactive or deleted users', function () {
    $viewer = User::factory()->create(['onboarded_at' => now()]);
    User::factory()->create(['name' => 'Hidden Inactive', 'active' => false]);
    User::factory()->create(['name' => 'Hidden Deleted', 'active' => true])->delete();

    $this->actingAs($viewer)
        ->getJson(route('command-palette.search', ['query' => 'Hidden']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'users');
});

it('requires at least two search characters', function () {
    $viewer = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($viewer)
        ->getJson(route('command-palette.search', ['query' => 'x']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('query');
});

it('honors the user directory department requirement', function () {
    ApplicationSetting::set('require_department_for_user_index', true, 'boolean');
    $viewer = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($viewer)
        ->getJson(route('command-palette.search', ['query' => 'fox']))
        ->assertForbidden();
});

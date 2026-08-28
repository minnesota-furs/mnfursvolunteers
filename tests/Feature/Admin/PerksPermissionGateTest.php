<?php

use App\Models\User;

it('blocks users without the Manage Volunteer Perks permission from the admin perks index', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('admin.perks.index'))
        ->assertForbidden();
});

it('no longer grants perks admin access via Manage Volunteer Events alone', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);

    $this->actingAs($user)
        ->get(route('admin.perks.index'))
        ->assertForbidden();
});

it('allows users with the Manage Volunteer Perks permission into the admin perks index', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Perks'],
    ]);

    $this->actingAs($user)
        ->get(route('admin.perks.index'))
        ->assertOk();
});

it('allows users with the Manage Volunteer Perks permission into the admin perk sets index', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Perks'],
    ]);

    $this->actingAs($user)
        ->get(route('admin.perk-sets.index'))
        ->assertOk();
});

it('shows the Volunteer Perks nav link only to users with the new permission', function () {
    $withPermission = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Perks'],
    ]);
    $withoutPermission = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);

    $this->actingAs($withPermission)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('admin.perks.index'), false);

    $this->actingAs($withoutPermission)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('admin.perks.index'), false);
});

<?php

use App\Models\Department;
use App\Models\User;

it('blocks a permissioned user with no departments from the events index', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);

    $this->actingAs($user)
        ->get(route('admin.events.index'))
        ->assertForbidden();
});

it('allows a permissioned user with a department assigned', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);
    $department = Department::factory()->create();
    $user->departments()->attach($department);

    $this->actingAs($user)
        ->get(route('admin.events.index'))
        ->assertOk();
});

it('exempts admins from the department requirement', function () {
    $admin = User::factory()->admin()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.events.index'))
        ->assertOk();
});

it('hides the Volunteer Events nav link for a permissioned user with no departments', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('tour-volunteer-events-link', false);
});

it('shows the Volunteer Events nav link for a permissioned user with a department', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);
    $department = Department::factory()->create();
    $user->departments()->attach($department);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('tour-volunteer-events-link', false);
});

it('shows the Volunteer Events nav link to admins with no departments', function () {
    $admin = User::factory()->admin()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('tour-volunteer-events-link', false);
});

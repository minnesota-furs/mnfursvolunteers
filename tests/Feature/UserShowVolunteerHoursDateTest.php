<?php

use App\Models\User;
use App\Models\VolunteerHours;

it('shows a short date beneath the relative task date for logged hours', function () {
    $admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $admin->givePermission('Manage Users');
    $user = User::factory()->create(['onboarded_at' => now()]);

    $volunteerHour = VolunteerHours::factory()->for($user)->create([
        'volunteer_date' => now()->subDays(10),
    ]);

    $this->actingAs($admin)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee($volunteerHour->volunteer_date->diffForHumans())
        ->assertSee($volunteerHour->volunteer_date->format('M j, Y'));
});

<?php

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;

it('shows shifts that conflict with the logged in users accessibility needs', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'accessibility_needs' => [
            'Limited standing/walking',
            'Service animal',
        ],
    ]);
    $event = Event::factory()->upcoming()->create();
    Shift::factory()->for($event)->create([
        'name' => 'Standing Registration',
        'accessibility_conflicts' => [
            'Limited standing/walking',
            'Cannot lift heavy objects',
        ],
    ]);

    $this->actingAs($user)
        ->get(route('volunteer.events.show', $event))
        ->assertOk()
        ->assertSee('Accessibility concerns identified')
        ->assertSee('May conflict with your accessibility needs:')
        ->assertSee('Limited standing/walking')
        ->assertDontSee('Cannot lift heavy objects');
});

it('does not show an accessibility warning when the shift does not conflict with the user', function () {
    $user = User::factory()->create([
        'onboarded_at' => now(),
        'accessibility_needs' => ['Deaf'],
    ]);
    $event = Event::factory()->upcoming()->create();
    Shift::factory()->for($event)->create([
        'accessibility_conflicts' => ['Service animal'],
    ]);

    $this->actingAs($user)
        ->get(route('volunteer.events.show', $event))
        ->assertOk()
        ->assertDontSee('Accessibility concerns identified')
        ->assertDontSee('May conflict with your accessibility needs:');
});

<?php

use App\Models\User;

it('shows accessibility needs after the profile onboarding step', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    $this->actingAs($user)
        ->get(route('onboarding.index', ['step' => 2]))
        ->assertOk()
        ->assertSee('Accessibility needs')
        ->assertSee('Wheelchair accessible')
        ->assertSee('No Accessibility Needs');
});

it('ends the onboarding progress bar at the final step marker', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    $this->actingAs($user)
        ->get(route('onboarding.index'))
        ->assertOk()
        ->assertSee('class="flex-none flex items-center', false);
});

it('stores selected accessibility needs during onboarding', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    $this->actingAs($user)
        ->post(route('onboarding.accessibility-needs'), [
            'has_accessibility_needs' => '1',
            'accessibility_needs' => [
                'Limited standing/walking',
                'Service animal',
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('onboarding.index', ['step' => 3]));

    expect($user->refresh()->accessibility_needs)->toBe([
        'Limited standing/walking',
        'Service animal',
    ]);
});

it('allows a user to select no accessibility needs during onboarding', function () {
    $user = User::factory()->create([
        'onboarded_at' => null,
        'accessibility_needs' => ['Deaf'],
    ]);

    $this->actingAs($user)
        ->post(route('onboarding.accessibility-needs'), [
            'has_accessibility_needs' => '0',
            'accessibility_needs' => ['Deaf'],
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->accessibility_needs)->toBe([]);
});

it('updates accessibility needs from the profile page', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Accessibility Needs')
        ->assertSee('Visually impaired');

    $this->actingAs($user)
        ->patch(route('profile.accessibility-needs'), [
            'has_accessibility_needs' => '1',
            'accessibility_needs' => ['Visually impaired'],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->accessibility_needs)->toBe(['Visually impaired']);
});

it('rejects unsupported accessibility needs', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->patch(route('profile.accessibility-needs'), [
            'has_accessibility_needs' => '1',
            'accessibility_needs' => ['Unsupported option'],
        ])
        ->assertSessionHasErrors('accessibility_needs.0');

    expect($user->refresh()->accessibility_needs)->toBeNull();
});

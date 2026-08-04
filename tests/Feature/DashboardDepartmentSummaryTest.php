<?php

use App\Models\Department;
use App\Models\Sector;
use App\Models\User;

it('collapses department assignments beyond the first two on the dashboard', function () {
    $sector = Sector::factory()->create(['name' => 'Convention Operations']);
    $departments = collect(['Registration', 'Security', 'Logistics', 'Hospitality'])
        ->map(fn (string $name) => Department::factory()->for($sector)->create(['name' => $name]));
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach($departments);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('aria-label="Your departments"', false)
        ->assertSeeTextInOrder(['Registration', 'Security', 'Logistics', 'Hospitality'])
        ->assertSeeText('Show 2 more')
        ->assertSee('x-show="expanded"', false)
        ->assertSee('x-bind:aria-expanded="expanded"', false);
});

it('shows two department assignments without an expansion control', function () {
    $departments = Department::factory()->count(2)->create();
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach($departments);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSeeText($departments[0]->name)
        ->assertSeeText($departments[1]->name)
        ->assertDontSeeText('Show more')
        ->assertDontSee('x-bind:aria-expanded="expanded"', false);
});

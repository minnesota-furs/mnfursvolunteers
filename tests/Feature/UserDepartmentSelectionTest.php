<?php

use App\Models\Department;
use App\Models\Sector;
use App\Models\User;

it('shows an accessible sector-grouped department picker when editing a user', function () {
    $administrator = User::factory()->admin()->create();
    $user = User::factory()->create();
    $operations = Sector::factory()->create(['name' => 'Operations']);
    $community = Sector::factory()->create(['name' => 'Community']);
    $selectedDepartment = Department::factory()->for($operations)->create(['name' => 'Registration']);
    $availableDepartment = Department::factory()->for($community)->create(['name' => 'Outreach']);
    $user->departments()->attach($selectedDepartment);

    $this->actingAs($administrator)
        ->get(route('users.edit', $user))
        ->assertOk()
        ->assertSeeText('Choose every department this user staffs.')
        ->assertSee('aria-label="Search departments or sectors"', false)
        ->assertSee('x-model.debounce.150ms="query"', false)
        ->assertSee('max-h-80', false)
        ->assertSeeText('No departments match your search.')
        ->assertSeeTextInOrder(['Community', 'Outreach', 'Operations', 'Registration'])
        ->assertSee('name="departments[]"', false)
        ->assertSee('value="'.$selectedDepartment->id.'"', false)
        ->assertSee('value="'.$availableDepartment->id.'"', false)
        ->assertSee('checked', false)
        ->assertSee('x-text="selectedDepartments.length"', false)
        ->assertSeeText('Clear')
        ->assertDontSeeText('Hold down the Ctrl');
});

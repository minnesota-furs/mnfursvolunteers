<?php

use App\Models\Department;
use App\Models\Event;
use App\Models\Sector;
use App\Models\Shift;
use App\Models\User;

it('allows any department in a required sector to satisfy event eligibility', function () {
    $requiredSector = Sector::factory()->create();
    $department = Department::factory()->for($requiredSector)->create();
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach($department);

    $event = Event::factory()->create();
    $event->requiredSectors()->attach($requiredSector);

    expect($event->userMeetsDepartmentRequirement($user))->toBeTrue();
});

it('keeps sector eligibility current when departments are added later', function () {
    $requiredSector = Sector::factory()->create();
    $event = Event::factory()->create();
    $event->requiredSectors()->attach($requiredSector);

    $newDepartment = Department::factory()->for($requiredSector)->create();
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach($newDepartment);

    expect($event->userMeetsDepartmentRequirement($user))->toBeTrue();
});

it('rejects a volunteer outside the required departments and sectors', function () {
    $requiredSector = Sector::factory()->create();
    $otherDepartment = Department::factory()->create();
    $user = User::factory()->create();
    $user->departments()->attach($otherDepartment);

    $event = Event::factory()->create();
    $event->requiredSectors()->attach($requiredSector);

    expect($event->userMeetsDepartmentRequirement($user))->toBeFalse();
});

it('allows a volunteer in a required sector to sign up for a shift', function () {
    $requiredSector = Sector::factory()->create();
    $department = Department::factory()->for($requiredSector)->create();
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach($department);

    $event = Event::factory()->upcoming()->create();
    $event->requiredSectors()->attach($requiredSector);
    $shift = Shift::factory()->for($event)->create([
        'start_time' => $event->start_date,
        'end_time' => $event->start_date->copy()->addHour(),
    ]);

    $this->actingAs($user)
        ->from(route('volunteer.events.show', $event))
        ->post(route('shifts.signup', $shift))
        ->assertRedirect(route('volunteer.events.show', $event));

    $this->assertDatabaseHas('shift_signups', [
        'shift_id' => $shift->id,
        'user_id' => $user->id,
    ]);
});

it('stores and validates sector restrictions when managing an event', function () {
    $manager = User::factory()->create([
        'onboarded_at' => now(),
        'permissions' => ['Manage Volunteer Events'],
    ]);
    $sector = Sector::factory()->create();

    $response = $this->actingAs($manager)->post(route('admin.events.store'), [
        'name' => 'Sector Restricted Event',
        'start_date' => now()->addWeek()->toDateTimeString(),
        'end_date' => now()->addWeek()->addDay()->toDateTimeString(),
        'visibility' => 'public',
        'required_sectors' => [$sector->id],
    ]);

    $response->assertRedirect(route('admin.events.index'));

    $event = Event::query()->where('name', 'Sector Restricted Event')->firstOrFail();
    expect($event->requiredSectors)->toHaveCount(1)
        ->and($event->requiredSectors->first()->is($sector))->toBeTrue();

    $this->actingAs($manager)->post(route('admin.events.store'), [
        'name' => 'Invalid Sector Event',
        'start_date' => now()->addWeek()->toDateTimeString(),
        'end_date' => now()->addWeek()->addDay()->toDateTimeString(),
        'visibility' => 'public',
        'required_sectors' => [PHP_INT_MAX],
    ])->assertSessionHasErrors('required_sectors.0');
});

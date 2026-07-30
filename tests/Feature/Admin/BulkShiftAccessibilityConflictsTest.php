<?php

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $this->event = Event::factory()->upcoming()->create();
});

it('shows accessibility conflict choices in the bulk shift editor', function () {
    Shift::factory()->for($this->event)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.index', $this->event))
        ->assertOk()
        ->assertSee('Update Accessibility Conflicts')
        ->assertSee('Wheelchair accessible')
        ->assertSee('Visually impaired');
});

it('bulk updates accessibility conflicts on selected shifts', function () {
    $selectedShifts = Shift::factory()->count(2)->for($this->event)->create();
    $unchangedShift = Shift::factory()->for($this->event)->create([
        'accessibility_conflicts' => ['Deaf'],
    ]);

    $this->actingAs($this->admin)
        ->patch(route('admin.events.shifts.bulk-update', $this->event), [
            'shift_ids' => $selectedShifts->pluck('id')->all(),
            'apply_accessibility_conflicts' => '1',
            'accessibility_conflicts' => [
                'Limited standing/walking',
                'Service animal',
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.shifts.index', $this->event));

    expect($selectedShifts->fresh()->pluck('accessibility_conflicts')->all())->each->toBe([
        'Limited standing/walking',
        'Service animal',
    ]);
    expect($unchangedShift->refresh()->accessibility_conflicts)->toBe(['Deaf']);
});

it('bulk clears accessibility conflicts when no concerns are selected', function () {
    $shifts = Shift::factory()->count(2)->for($this->event)->create([
        'accessibility_conflicts' => ['Visually impaired'],
    ]);

    $this->actingAs($this->admin)
        ->patch(route('admin.events.shifts.bulk-update', $this->event), [
            'shift_ids' => $shifts->pluck('id')->all(),
            'apply_accessibility_conflicts' => '1',
        ])
        ->assertSessionHasNoErrors();

    expect($shifts->fresh()->pluck('accessibility_conflicts')->all())->each->toBe([]);
});

it('rejects unsupported accessibility conflicts in a bulk update', function () {
    $shift = Shift::factory()->for($this->event)->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.events.shifts.bulk-update', $this->event), [
            'shift_ids' => [$shift->id],
            'apply_accessibility_conflicts' => '1',
            'accessibility_conflicts' => ['Unsupported option'],
        ])
        ->assertSessionHasErrors('accessibility_conflicts.0');

    expect($shift->refresh()->accessibility_conflicts)->toBeNull();
});

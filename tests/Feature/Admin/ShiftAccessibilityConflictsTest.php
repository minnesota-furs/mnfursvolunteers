<?php

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->event = Event::factory()->create();
    $this->shift = Shift::factory()->for($this->event)->create();
});

it('shows accessibility conflict choices on the shift edit page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.edit', [$this->event, $this->shift]))
        ->assertOk()
        ->assertSee('Accessibility Conflicts')
        ->assertSee('Wheelchair accessible')
        ->assertSee('Visually impaired');
});

it('allows an admin to define accessibility conflicts for a shift', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.events.shifts.update', [$this->event, $this->shift]), [
            'name' => $this->shift->name,
            'description' => $this->shift->description,
            'start_time' => $this->shift->start_time->format('Y-m-d H:i:s'),
            'end_time' => $this->shift->end_time->format('Y-m-d H:i:s'),
            'max_volunteers' => $this->shift->max_volunteers,
            'accessibility_conflicts' => [
                'Limited standing/walking',
                'Cannot lift heavy objects',
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.shifts.index', $this->event));

    expect($this->shift->refresh()->accessibility_conflicts)->toBe([
        'Limited standing/walking',
        'Cannot lift heavy objects',
    ]);
});

it('allows an admin to clear accessibility conflicts from a shift', function () {
    $this->shift->update(['accessibility_conflicts' => ['Deaf']]);

    $this->actingAs($this->admin)
        ->put(route('admin.events.shifts.update', [$this->event, $this->shift]), [
            'name' => $this->shift->name,
            'description' => $this->shift->description,
            'start_time' => $this->shift->start_time->format('Y-m-d H:i:s'),
            'end_time' => $this->shift->end_time->format('Y-m-d H:i:s'),
            'max_volunteers' => $this->shift->max_volunteers,
        ])
        ->assertSessionHasNoErrors();

    expect($this->shift->refresh()->accessibility_conflicts)->toBe([]);
});

it('rejects unsupported accessibility conflicts', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.events.shifts.update', [$this->event, $this->shift]), [
            'name' => $this->shift->name,
            'description' => $this->shift->description,
            'start_time' => $this->shift->start_time->format('Y-m-d H:i:s'),
            'end_time' => $this->shift->end_time->format('Y-m-d H:i:s'),
            'max_volunteers' => $this->shift->max_volunteers,
            'accessibility_conflicts' => ['Unsupported option'],
        ])
        ->assertSessionHasErrors('accessibility_conflicts.0');

    expect($this->shift->refresh()->accessibility_conflicts)->toBeNull();
});

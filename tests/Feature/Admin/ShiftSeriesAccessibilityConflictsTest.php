<?php

use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $this->event = Event::factory()->upcoming()->create();
});

it('shows accessibility conflict choices on the create shift series page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.create-series', $this->event))
        ->assertOk()
        ->assertSee('Accessibility Conflicts')
        ->assertSee('Wheelchair accessible')
        ->assertSee('Visually impaired');
});

it('applies accessibility conflicts to every shift in a new series', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.events.shifts.store-series', $this->event), validShiftSeriesData([
            'occurrences' => 3,
            'accessibility_conflicts' => [
                'Limited standing/walking',
                'Cannot lift heavy objects',
            ],
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.shifts.index', $this->event));

    $shifts = $this->event->shifts()->orderBy('duplicate_sequence')->get();

    expect($shifts)->toHaveCount(3);
    expect($shifts->pluck('accessibility_conflicts')->all())->each->toBe([
        'Limited standing/walking',
        'Cannot lift heavy objects',
    ]);
});

it('rejects unsupported accessibility conflicts for a shift series', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.events.shifts.store-series', $this->event), validShiftSeriesData([
            'accessibility_conflicts' => ['Unsupported option'],
        ]))
        ->assertSessionHasErrors('accessibility_conflicts.0');

    expect($this->event->shifts()->count())->toBe(0);
});

function validShiftSeriesData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Registration',
        'naming_pattern' => '{name} - {start_time}',
        'description' => 'Welcome volunteers.',
        'start_time' => now()->addWeek()->format('Y-m-d H:i:s'),
        'duration_hours' => 2,
        'duration_minutes' => 0,
        'occurrences' => 2,
        'gap_hours' => 0,
        'gap_minutes' => 30,
        'max_volunteers' => 4,
    ], $overrides);
}

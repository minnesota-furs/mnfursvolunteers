<?php

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;

it('logs the created shift ids and series id when a shift series is created', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    $this->actingAs($manager);

    $event = Event::factory()->create();

    $response = $this->post(route('admin.events.shifts.store-series', $event), [
        'name' => 'Registration Desk',
        'naming_pattern' => '{name} ({n})',
        'start_time' => $event->start_date->format('Y-m-d H:i:s'),
        'duration_hours' => 1,
        'duration_minutes' => 0,
        'occurrences' => 3,
        'gap_hours' => 0,
        'gap_minutes' => 0,
        'max_volunteers' => 2,
    ]);

    $response->assertRedirect(route('admin.events.shifts.index', $event));

    $createdShiftIds = Shift::where('event_id', $event->id)->pluck('id')->sort()->values()->all();
    expect($createdShiftIds)->toHaveCount(3);

    $log = AuditLog::where('action', 'shift_series_created')
        ->where('auditable_id', $event->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->changes['count'])->toBe(3);
    expect($log->changes['name'])->toBe('Registration Desk');
    expect($log->changes['series_id'])->not->toBeEmpty();
    expect(collect($log->changes['shift_ids'])->sort()->values()->all())->toBe($createdShiftIds);

    // The series id logged matches what was actually stamped onto the shifts,
    // which is what an undo feature would use to find them later.
    expect(Shift::whereIn('id', $createdShiftIds)->pluck('duplicate_series_id')->unique()->all())
        ->toBe([$log->changes['series_id']]);
});

function createShiftSeries(User $manager, Event $event, string $name = 'Registration Desk', int $occurrences = 3): AuditLog
{
    test()->actingAs($manager)->post(route('admin.events.shifts.store-series', $event), [
        'name' => $name,
        'naming_pattern' => '{name} ({n})',
        'start_time' => $event->start_date->format('Y-m-d H:i:s'),
        'duration_hours' => 1,
        'duration_minutes' => 0,
        'occurrences' => $occurrences,
        'gap_hours' => 0,
        'gap_minutes' => 0,
        'max_volunteers' => 2,
    ]);

    return AuditLog::where('action', 'shift_series_created')->where('auditable_id', $event->id)->latest('id')->first();
}

it('shows a recent series banner with an undo button on the shift index', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    $event = Event::factory()->create();

    createShiftSeries($manager, $event, 'Registration Desk');

    $this->actingAs($manager)
        ->get(route('admin.events.shifts.index', $event))
        ->assertOk()
        ->assertSee('Registration Desk')
        ->assertSee('Undo');
});

it('undoes a shift series created within the last 6 hours', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    $event = Event::factory()->create();

    $log = createShiftSeries($manager, $event, 'Registration Desk');
    $shiftIds = $log->changes['shift_ids'];
    expect(Shift::whereIn('id', $shiftIds)->count())->toBe(3);

    $response = $this->actingAs($manager)
        ->post(route('admin.events.shifts.undo-series', [$event, $log]));

    $response->assertRedirect(route('admin.events.shifts.index', $event));
    $response->assertSessionHas('success');
    expect(Shift::whereIn('id', $shiftIds)->count())->toBe(0);

    $undoLog = AuditLog::where('action', 'shift_series_undone')->where('auditable_id', $event->id)->latest('id')->first();
    expect($undoLog)->not->toBeNull();
    expect($undoLog->changes['count'])->toBe(3);

    // Once undone, the banner should no longer offer to undo the same series again.
    // (An intermediate request burns off the one-time "Undid the ... series" success
    // flash from the redirect above, which would otherwise also mention the name.)
    $this->actingAs($manager)->get(route('admin.events.shifts.index', $event));
    $this->actingAs($manager)
        ->get(route('admin.events.shifts.index', $event))
        ->assertOk()
        ->assertDontSee(route('admin.events.shifts.undo-series', [$event, $log]), false);
});

it('refuses to undo a series older than 6 hours', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    $event = Event::factory()->create();

    $log = createShiftSeries($manager, $event, 'Registration Desk');
    $shiftIds = $log->changes['shift_ids'];
    $log->forceFill(['created_at' => now()->subHours(7)])->save();

    $response = $this->actingAs($manager)
        ->post(route('admin.events.shifts.undo-series', [$event, $log]));

    $response->assertSessionHas('error');
    expect(Shift::whereIn('id', $shiftIds)->count())->toBe(3);
});

it('refuses to undo a series if any of its shifts already have volunteers signed up', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    $event = Event::factory()->create();
    $volunteer = User::factory()->create();

    $log = createShiftSeries($manager, $event, 'Registration Desk');
    $shiftIds = $log->changes['shift_ids'];
    Shift::find($shiftIds[0])->users()->attach($volunteer->id, ['signed_up_at' => now()]);

    $response = $this->actingAs($manager)
        ->post(route('admin.events.shifts.undo-series', [$event, $log]));

    $response->assertSessionHas('error');
    expect(Shift::whereIn('id', $shiftIds)->count())->toBe(3);
});

it('dismisses a series banner without touching its shifts', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    $event = Event::factory()->create();

    $log = createShiftSeries($manager, $event, 'Registration Desk');
    $shiftIds = $log->changes['shift_ids'];

    $this->actingAs($manager)
        ->post(route('admin.events.shifts.dismiss-series', [$event, $log]))
        ->assertRedirect();

    expect(Shift::whereIn('id', $shiftIds)->count())->toBe(3);
    expect($log->fresh()->changes['dismissed'])->toBeTrue();

    // The dismiss button itself only ever appears on the banner, so its
    // absence confirms the banner entry for this series is gone — even
    // though the series still legitimately appears (with an Undo option)
    // in the separate "Recent Series Creations" history modal.
    $this->actingAs($manager)
        ->get(route('admin.events.shifts.index', $event))
        ->assertOk()
        ->assertDontSee(route('admin.events.shifts.dismiss-series', [$event, $log]), false);
});

it('does not let one event manager undo a series belonging to a different event', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    $eventOne = Event::factory()->create();
    $eventTwo = Event::factory()->create();

    $log = createShiftSeries($manager, $eventOne, 'Registration Desk');

    $this->actingAs($manager)
        ->post(route('admin.events.shifts.undo-series', [$eventTwo, $log]))
        ->assertNotFound();
});

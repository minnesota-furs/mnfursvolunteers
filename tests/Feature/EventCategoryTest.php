<?php

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Shift;
use App\Models\User;

function actingAsEventManager(): User
{
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('manage-volunteer-events');
    test()->actingAs($manager);

    return $manager;
}

it('lets a manager create an event category scoped to a specific event', function () {
    actingAsEventManager();
    $event = Event::factory()->create();

    $response = $this->post(route('admin.events.categories.store', $event), [
        'name' => 'Badge Checker',
        'color' => '#FF0000',
        'description' => 'Checks badges at the door',
    ]);

    $response->assertRedirect(route('admin.events.categories.index', $event));
    $this->assertDatabaseHas('event_categories', [
        'event_id' => $event->id,
        'name' => 'Badge Checker',
        'color' => '#FF0000',
    ]);
});

it('allows the same category name on different events but not twice on the same event', function () {
    actingAsEventManager();
    $eventOne = Event::factory()->create();
    $eventTwo = Event::factory()->create();

    EventCategory::factory()->for($eventOne)->create(['name' => 'Setup']);

    // Same name on a different event is fine.
    $this->post(route('admin.events.categories.store', $eventTwo), ['name' => 'Setup'])
        ->assertRedirect(route('admin.events.categories.index', $eventTwo));
    $this->assertDatabaseHas('event_categories', ['event_id' => $eventTwo->id, 'name' => 'Setup']);

    // Duplicate name on the same event fails validation.
    $this->post(route('admin.events.categories.store', $eventOne), ['name' => 'Setup'])
        ->assertSessionHasErrors('name');
    $this->assertDatabaseCount('event_categories', 2);
});

it('does not list categories from other events', function () {
    actingAsEventManager();
    $eventOne = Event::factory()->create();
    $eventTwo = Event::factory()->create();

    EventCategory::factory()->for($eventOne)->create(['name' => 'Teardown']);
    EventCategory::factory()->for($eventTwo)->create(['name' => 'Registration']);

    $this->get(route('admin.events.categories.index', $eventOne))
        ->assertOk()
        ->assertSee('Teardown')
        ->assertDontSee('Registration');
});

it('lets a manager update and delete an event category', function () {
    actingAsEventManager();
    $event = Event::factory()->create();
    $category = EventCategory::factory()->for($event)->create(['name' => 'Old Name']);

    $this->put(route('admin.events.categories.update', [$event, $category]), ['name' => 'New Name'])
        ->assertRedirect(route('admin.events.categories.index', $event));
    $this->assertDatabaseHas('event_categories', ['id' => $category->id, 'name' => 'New Name']);

    $this->delete(route('admin.events.categories.destroy', [$event, $category]))
        ->assertRedirect(route('admin.events.categories.index', $event));
    $this->assertDatabaseMissing('event_categories', ['id' => $category->id]);
});

it('blocks a user without manage permission from creating event categories', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $event = Event::factory()->create();
    $this->actingAs($user);

    $this->post(route('admin.events.categories.store', $event), ['name' => 'Badge Checker'])
        ->assertForbidden();
    $this->assertDatabaseMissing('event_categories', ['name' => 'Badge Checker']);
});

it('syncs event categories onto a shift when creating and updating it', function () {
    actingAsEventManager();
    $event = Event::factory()->create();
    $categoryOne = EventCategory::factory()->for($event)->create(['name' => 'Setup']);
    $categoryTwo = EventCategory::factory()->for($event)->create(['name' => 'Teardown']);

    $response = $this->post(route('admin.events.shifts.store', $event), [
        'name' => 'Morning Shift',
        'start_time' => $event->start_date->format('Y-m-d H:i:s'),
        'end_time' => $event->start_date->copy()->addHours(2)->format('Y-m-d H:i:s'),
        'max_volunteers' => 5,
        'event_category_ids' => [$categoryOne->id],
    ]);
    $response->assertRedirect(route('admin.events.shifts.index', $event));

    $shift = Shift::where('name', 'Morning Shift')->firstOrFail();
    expect($shift->categories->pluck('id')->all())->toBe([$categoryOne->id]);

    $this->put(route('admin.events.shifts.update', [$event, $shift]), [
        'name' => 'Morning Shift',
        'start_time' => $shift->start_time->format('Y-m-d H:i:s'),
        'end_time' => $shift->end_time->format('Y-m-d H:i:s'),
        'max_volunteers' => 5,
        'event_category_ids' => [$categoryTwo->id],
    ])->assertRedirect(route('admin.events.shifts.index', $event));

    expect($shift->fresh()->categories->pluck('id')->all())->toBe([$categoryTwo->id]);
});

it('rejects a category id belonging to a different event when creating a shift', function () {
    actingAsEventManager();
    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();
    $foreignCategory = EventCategory::factory()->for($otherEvent)->create();

    $this->post(route('admin.events.shifts.store', $event), [
        'name' => 'Morning Shift',
        'start_time' => $event->start_date->format('Y-m-d H:i:s'),
        'end_time' => $event->start_date->copy()->addHours(2)->format('Y-m-d H:i:s'),
        'max_volunteers' => 5,
        'event_category_ids' => [$foreignCategory->id],
    ])->assertSessionHasErrors('event_category_ids.0');

    $this->assertDatabaseMissing('shifts', ['name' => 'Morning Shift']);
});

it('shows a shift category badge to volunteers on the event shift list and shift detail page', function () {
    $volunteer = User::factory()->create(['onboarded_at' => now()]);
    $event = Event::factory()->upcoming()->create();
    $category = EventCategory::factory()->for($event)->create(['name' => 'Badge Checker']);
    $shift = Shift::factory()->for($event)->create();
    $shift->categories()->attach($category);

    $this->actingAs($volunteer)
        ->get(route('volunteer.events.show', $event))
        ->assertOk()
        ->assertSee('Badge Checker');

    $this->actingAs($volunteer)
        ->get(route('volunteer.shifts.show', [$event, $shift]))
        ->assertOk()
        ->assertSee('Badge Checker');
});

it('shows a category filter dropdown on the event shift list only when a shift has a category', function () {
    $volunteer = User::factory()->create(['onboarded_at' => now()]);
    $event = Event::factory()->upcoming()->create();
    $category = EventCategory::factory()->for($event)->create(['name' => 'Badge Checker']);
    $shift = Shift::factory()->for($event)->create();
    $shift->categories()->attach($category);

    $this->actingAs($volunteer)
        ->get(route('volunteer.events.show', $event))
        ->assertOk()
        ->assertSee('All categories')
        ->assertSee('value="'.$category->id.'"', false);
});

it('does not show the category filter dropdown when no shift has a category', function () {
    $volunteer = User::factory()->create(['onboarded_at' => now()]);
    $event = Event::factory()->upcoming()->create();
    Shift::factory()->for($event)->create();

    $this->actingAs($volunteer)
        ->get(route('volunteer.events.show', $event))
        ->assertOk()
        ->assertDontSee('All categories');
});

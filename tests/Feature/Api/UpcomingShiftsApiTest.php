<?php

use App\Models\ApplicationSetting;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Shift;

it('returns upcoming shifts for a public event', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertSuccessful()
        ->assertJson(['status' => 'success']);
});

it('returns upcoming shifts for an unlisted event', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'unlisted']);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertSuccessful()
        ->assertJson(['status' => 'success']);
});

it('returns not found for a draft event', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'draft']);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertNotFound();
});

it('returns not found for an internal event', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'internal']);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertNotFound();
});

it('includes a shift\'s categories when set', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);
    $category = EventCategory::factory()->for($event)->create(['name' => 'Registration', 'color' => '#ff0000']);

    $shift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $shift->categories()->attach($category);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertSuccessful();
    $data = collect($response->json('data'))->firstWhere('id', $shift->id);

    expect($data['categories'])->toBe([
        ['name' => 'Registration', 'color' => '#ff0000'],
    ]);
});

it('returns an empty categories array when a shift has none', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $shift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertSuccessful();
    $data = collect($response->json('data'))->firstWhere('id', $shift->id);

    expect($data['categories'])->toBe([]);
});

it('filters shifts by a single categoryId', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);
    $registration = EventCategory::factory()->for($event)->create(['name' => 'Registration']);
    $setup = EventCategory::factory()->for($event)->create(['name' => 'Setup']);

    $registrationShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $registrationShift->categories()->attach($registration);

    $setupShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $setupShift->categories()->attach($setup);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming?categoryId={$registration->id}");

    $response->assertSuccessful();
    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($registrationShift->id);
    expect($ids)->not->toContain($setupShift->id);
});

it('filters shifts by multiple comma-separated categoryIds', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);
    $registration = EventCategory::factory()->for($event)->create(['name' => 'Registration']);
    $setup = EventCategory::factory()->for($event)->create(['name' => 'Setup']);
    $teardown = EventCategory::factory()->for($event)->create(['name' => 'Teardown']);

    $registrationShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $registrationShift->categories()->attach($registration);

    $setupShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $setupShift->categories()->attach($setup);

    $teardownShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $teardownShift->categories()->attach($teardown);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming?categoryId={$registration->id},{$setup->id}");

    $response->assertSuccessful();
    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($registrationShift->id);
    expect($ids)->toContain($setupShift->id);
    expect($ids)->not->toContain($teardownShift->id);
});

it('filters shifts by categorySearch matching part of a category name', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);
    $badgeChecker = EventCategory::factory()->for($event)->create(['name' => 'Badge Checker']);
    $setup = EventCategory::factory()->for($event)->create(['name' => 'Setup']);

    $badgeShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $badgeShift->categories()->attach($badgeChecker);

    $setupShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);
    $setupShift->categories()->attach($setup);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming?categorySearch=Badge");

    $response->assertSuccessful();
    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($badgeShift->id);
    expect($ids)->not->toContain($setupShift->id);
});

it('includes a shift\'s accessibility conflicts when set', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $shift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
        'accessibility_conflicts' => ['Limited standing/walking', 'Deaf'],
    ]);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertSuccessful();
    $data = collect($response->json('data'))->firstWhere('id', $shift->id);

    expect($data['accessibility_conflicts'])->toBe(['Limited standing/walking', 'Deaf']);
});

it('returns an empty accessibility_conflicts array when a shift has none', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $shift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertSuccessful();
    $data = collect($response->json('data'))->firstWhere('id', $shift->id);

    expect($data['accessibility_conflicts'])->toBe([]);
});

it('allows a limit above the old 100 cap, up to 500', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    Shift::factory()->for($event)->count(150)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming?limit=150");

    $response->assertSuccessful();
    expect(collect($response->json('data')))->toHaveCount(150);
});

it('caps the limit at 500 even when a higher limit is requested', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    Shift::factory()->for($event)->count(510)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming?limit=1000");

    $response->assertSuccessful();
    expect(collect($response->json('data')))->toHaveCount(500);
});

it('hides accessibility conflicts when disclosures are disabled', function () {
    ApplicationSetting::set('feature_accessibility_disclosures', false, 'boolean', group: 'feature_flags');

    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $shift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
        'accessibility_conflicts' => ['Deaf'],
    ]);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming");

    $response->assertSuccessful();
    $data = collect($response->json('data'))->firstWhere('id', $shift->id);

    expect($data['accessibility_conflicts'])->toBe([]);
});

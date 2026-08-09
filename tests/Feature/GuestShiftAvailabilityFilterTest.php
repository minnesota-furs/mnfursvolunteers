<?php

use App\Models\Event;
use App\Models\Shift;
use App\Models\User;

it('filters guest shift listing to open shifts without a group by SQL error', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $fullShift = Shift::factory()->for($event)->create(['max_volunteers' => 1]);
    $fullShift->users()->attach(User::factory()->create());

    $openShift = Shift::factory()->for($event)->create(['max_volunteers' => 2]);
    $openShift->users()->attach(User::factory()->create());

    $response = $this->get(route('vol-listings-public.show', $event) . '?availability=open');

    $response->assertSuccessful();
    $response->assertSee($openShift->name);
    $response->assertDontSee($fullShift->name);
});

it('shows related open shifts on the guest shift detail page without a group by SQL error', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $shift = Shift::factory()->for($event)->create(['max_volunteers' => 2]);

    $fullRelatedShift = Shift::factory()->for($event)->create(['max_volunteers' => 1]);
    $fullRelatedShift->users()->attach(User::factory()->create());

    $openRelatedShift = Shift::factory()->for($event)->create(['max_volunteers' => 2]);

    $response = $this->get(route('vol-listings-public.shift.show', [$event, $shift]));

    $response->assertSuccessful();
    $response->assertSee($openRelatedShift->name);
    $response->assertDontSee($fullRelatedShift->name);
});

it('filters the upcoming shifts API to open slots without a group by SQL error', function () {
    $event = Event::factory()->upcoming()->create(['visibility' => 'public']);

    $fullShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
        'max_volunteers' => 1,
    ]);
    $fullShift->users()->attach(User::factory()->create());

    $openShift = Shift::factory()->for($event)->create([
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
        'max_volunteers' => 2,
    ]);

    $response = $this->getJson("/api/events/{$event->id}/shifts/upcoming?openSlotsOnly=1");

    $response->assertSuccessful();
    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($openShift->id);
    expect($ids)->not->toContain($fullShift->id);
});

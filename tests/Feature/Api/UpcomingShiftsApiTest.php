<?php

use App\Models\Event;

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

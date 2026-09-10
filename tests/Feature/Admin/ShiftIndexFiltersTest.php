<?php

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Shift;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $this->event = Event::factory()->create();
});

it('filters the shift index by search term', function () {
    $matching = Shift::factory()->for($this->event)->create(['name' => 'Registration Desk']);
    $other = Shift::factory()->for($this->event)->create(['name' => 'Green Room Support']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.index', $this->event).'?search=Registration');

    $response->assertOk()
        ->assertSee($matching->name)
        ->assertDontSee($other->name);
});

it('filters the shift index by category', function () {
    $matchingCategory = EventCategory::factory()->for($this->event)->create();
    $otherCategory = EventCategory::factory()->for($this->event)->create();

    $matching = Shift::factory()->for($this->event)->create(['name' => 'Badge Checking']);
    $matching->categories()->attach($matchingCategory);

    $other = Shift::factory()->for($this->event)->create(['name' => 'Floater']);
    $other->categories()->attach($otherCategory);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.index', $this->event).'?category='.$matchingCategory->id);

    $response->assertOk()
        ->assertSee($matching->name)
        ->assertDontSee($other->name);
});

it('filters the shift index by tag', function () {
    $matchingTag = Tag::create(['name' => 'Lead Volunteer', 'type' => 'shift']);
    $otherTag = Tag::create(['name' => 'Trainee', 'type' => 'shift']);

    $matching = Shift::factory()->for($this->event)->create(['name' => 'Security Patrol']);
    $matching->tags()->attach($matchingTag);

    $other = Shift::factory()->for($this->event)->create(['name' => 'Merchandise Table']);
    $other->tags()->attach($otherTag);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.index', $this->event).'?tag='.$matchingTag->id);

    $response->assertOk()
        ->assertSee($matching->name)
        ->assertDontSee($other->name);
});

it('filters the shift index to double hours shifts only', function () {
    $doubleHours = Shift::factory()->for($this->event)->create(['name' => 'Overnight Watch', 'double_hours' => true]);
    $regular = Shift::factory()->for($this->event)->create(['name' => 'Daytime Watch', 'double_hours' => false]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.index', $this->event).'?double_hours=1');

    $response->assertOk()
        ->assertSee($doubleHours->name)
        ->assertDontSee($regular->name);
});

it('filters the shift index by staffing availability', function () {
    $fullShift = Shift::factory()->for($this->event)->create(['name' => 'Full Shift', 'max_volunteers' => 1]);
    $fullShift->users()->attach(User::factory()->create());

    $openShift = Shift::factory()->for($this->event)->create(['name' => 'Open Shift', 'max_volunteers' => 2]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.events.shifts.index', $this->event).'?availability=full');

    $response->assertOk()
        ->assertSee($fullShift->name)
        ->assertDontSee($openShift->name);
});

<?php

use App\Models\Event;
use App\Models\FiscalLedger;
use App\Models\User;
use App\Models\VolunteerHours;
use App\Models\VolunteerPerk;
use App\Models\VolunteerPerkSet;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->ledger = FiscalLedger::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);
    $this->set = VolunteerPerkSet::create([
        'name' => 'Con Perks',
        'is_active' => true,
        'sort_order' => 0,
    ]);
    $this->perk = VolunteerPerk::create([
        'perk_set_id' => $this->set->id,
        'name' => 'Free T-Shirt',
        'min_hours' => 5,
        'is_active' => true,
        'sort_order' => 0,
    ]);
    $this->perk->events()->attach(Event::factory()->create());
});

it('credits manually-logged hours toward an event-linked perk when attributed to its perk set', function () {
    VolunteerHours::create([
        'user_id' => $this->user->id,
        'hours' => 6,
        'counts_toward_perks' => true,
        'perk_set_id' => $this->set->id,
        'fiscal_ledger_id' => $this->ledger->id,
    ]);

    expect($this->perk->getUserProgress($this->user))->toBe(6.0)
        ->and($this->perk->hasEarned($this->user))->toBeTrue();
});

it('does not credit unattributed manually-logged hours toward an event-linked perk', function () {
    VolunteerHours::create([
        'user_id' => $this->user->id,
        'hours' => 6,
        'counts_toward_perks' => true,
        'perk_set_id' => null,
        'fiscal_ledger_id' => $this->ledger->id,
    ]);

    expect($this->perk->getUserProgress($this->user))->toBe(0.0)
        ->and($this->perk->hasEarned($this->user))->toBeFalse();
});

it('does not credit hours attributed to a different perk set toward an event-linked perk', function () {
    $otherSet = VolunteerPerkSet::create([
        'name' => 'Other Perks',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    VolunteerHours::create([
        'user_id' => $this->user->id,
        'hours' => 6,
        'counts_toward_perks' => true,
        'perk_set_id' => $otherSet->id,
        'fiscal_ledger_id' => $this->ledger->id,
    ]);

    expect($this->perk->getUserProgress($this->user))->toBe(0.0);
});

it('excludes attributed hours flagged as not counting toward perks from an event-linked perk', function () {
    VolunteerHours::create([
        'user_id' => $this->user->id,
        'hours' => 6,
        'counts_toward_perks' => false,
        'perk_set_id' => $this->set->id,
        'fiscal_ledger_id' => $this->ledger->id,
    ]);

    expect($this->perk->getUserProgress($this->user))->toBe(0.0);
});

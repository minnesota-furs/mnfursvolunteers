<?php

use App\Models\FiscalLedger;
use App\Models\User;
use App\Models\VolunteerHours;

it('creates a persistable volunteer hours record with a matching fiscal ledger', function () {
    FiscalLedger::factory()->create([
        'start_date' => now()->subYears(2),
        'end_date' => now(),
    ]);

    $user = User::factory()->create();

    $hours = VolunteerHours::factory()->for($user)->create();

    expect($hours)->toBeInstanceOf(VolunteerHours::class)
        ->and($hours->user_id)->toBe($user->id)
        ->and($hours->hours)->toBeGreaterThan(0)
        ->and($hours->fiscal_ledger_id)->not->toBeNull();
});

it('can generate many varied hour logs for a single user', function () {
    $user = User::factory()->create();

    $entries = VolunteerHours::factory()->count(10)->for($user)->create();

    expect($entries)->toHaveCount(10);
    expect($entries->pluck('volunteer_date')->unique()->count())->toBeGreaterThan(1);
});

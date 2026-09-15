<?php

use App\Models\FiscalLedger;
use App\Models\User;
use App\Models\VolunteerHours;
use App\Models\VolunteerPerk;
use App\Models\VolunteerPerkSet;

it('excludes hours flagged as not counting toward perks from progress calculations', function () {
    $user = User::factory()->create();
    $ledger = FiscalLedger::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);

    VolunteerHours::create([
        'user_id' => $user->id,
        'hours' => 5,
        'counts_toward_perks' => true,
        'fiscal_ledger_id' => $ledger->id,
    ]);

    VolunteerHours::create([
        'user_id' => $user->id,
        'hours' => 3,
        'counts_toward_perks' => false,
        'fiscal_ledger_id' => $ledger->id,
    ]);

    $set = VolunteerPerkSet::create([
        'name' => 'Test Perk Set',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $perk = VolunteerPerk::create([
        'perk_set_id' => $set->id,
        'name' => 'Free T-Shirt',
        'min_hours' => 5,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    expect($perk->getUserProgress($user))->toBe(5.0)
        ->and($perk->hasEarned($user))->toBeTrue();
});

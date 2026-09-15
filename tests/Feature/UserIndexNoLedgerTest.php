<?php

use App\Models\FiscalLedger;
use App\Models\User;

it('shows a setup message when no current fiscal ledger exists', function () {
    $admin = User::factory()->admin()->create(['onboarded_at' => now()]);

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertSuccessful();
    $response->assertSee('No current fiscal ledger found');
    $response->assertSee(route('ledger.create'), false);
});

it('lists users when a current fiscal ledger exists', function () {
    $admin = User::factory()->admin()->create(['onboarded_at' => now()]);

    FiscalLedger::factory()->create([
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
    ]);

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertSuccessful();
    $response->assertDontSee('No current fiscal ledger found');
});

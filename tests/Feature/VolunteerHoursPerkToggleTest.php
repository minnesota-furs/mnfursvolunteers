<?php

use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\User;
use App\Models\VolunteerHours;
use App\Models\VolunteerPerk;
use App\Models\VolunteerPerkSet;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $this->volunteer = User::factory()->create(['onboarded_at' => now()]);
    $this->department = Department::factory()->create();
    FiscalLedger::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);
});

function configureAnActivePerk(): VolunteerPerk
{
    $set = VolunteerPerkSet::create([
        'name' => 'Test Perk Set',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    return VolunteerPerk::create([
        'perk_set_id' => $set->id,
        'name' => 'Free T-Shirt',
        'min_hours' => 5,
        'is_active' => true,
        'sort_order' => 0,
    ]);
}

it('hides the counts toward perks toggle when no perks are configured', function () {
    $this->actingAs($this->admin)
        ->get(route('hours.create', $this->volunteer->id))
        ->assertOk()
        ->assertDontSee('counts_toward_perks', false);
});

it('shows the counts toward perks toggle when a perk is configured', function () {
    configureAnActivePerk();

    $this->actingAs($this->admin)
        ->get(route('hours.create', $this->volunteer->id))
        ->assertOk()
        ->assertSee('counts_toward_perks', false);
});

it('defaults new hours to counting toward perks when no toggle is shown', function () {
    $this->actingAs($this->admin)->post(route('hours.store'), [
        'user_id' => $this->volunteer->id,
        'hours' => 2,
        'notes' => '',
        'volunteer_date' => now()->toDateString(),
        'primary_dept_id' => $this->department->id,
    ]);

    expect(VolunteerHours::first()->counts_toward_perks)->toBeTrue();
});

it('stores counts_toward_perks as false when the box is left unchecked and perks are configured', function () {
    configureAnActivePerk();

    $this->actingAs($this->admin)->post(route('hours.store'), [
        'user_id' => $this->volunteer->id,
        'hours' => 2,
        'notes' => '',
        'volunteer_date' => now()->toDateString(),
        'primary_dept_id' => $this->department->id,
        // counts_toward_perks intentionally omitted, simulating an unchecked box
    ]);

    expect(VolunteerHours::first()->counts_toward_perks)->toBeFalse();
});

it('stores counts_toward_perks as true when the box is checked and perks are configured', function () {
    configureAnActivePerk();

    $this->actingAs($this->admin)->post(route('hours.store'), [
        'user_id' => $this->volunteer->id,
        'hours' => 2,
        'notes' => '',
        'volunteer_date' => now()->toDateString(),
        'primary_dept_id' => $this->department->id,
        'counts_toward_perks' => '1',
    ]);

    expect(VolunteerHours::first()->counts_toward_perks)->toBeTrue();
});

it('can turn off counts_toward_perks for an existing entry via update', function () {
    configureAnActivePerk();

    $hour = VolunteerHours::create([
        'user_id' => $this->volunteer->id,
        'hours' => 3,
        'primary_dept_id' => $this->department->id,
        'counts_toward_perks' => true,
        'fiscal_ledger_id' => FiscalLedger::first()->id,
    ]);

    $this->actingAs($this->admin)->patch(route('hours.update', $hour->id), [
        'user_id' => $this->volunteer->id,
        'hours' => 3,
        'primary_dept_id' => $this->department->id,
        // counts_toward_perks intentionally omitted, simulating an unchecked box
    ]);

    expect($hour->fresh()->counts_toward_perks)->toBeFalse();
});

it('leaves counts_toward_perks untouched on update when no perks are configured', function () {
    $hour = VolunteerHours::create([
        'user_id' => $this->volunteer->id,
        'hours' => 3,
        'primary_dept_id' => $this->department->id,
        'counts_toward_perks' => false,
        'fiscal_ledger_id' => FiscalLedger::first()->id,
    ]);

    $this->actingAs($this->admin)->patch(route('hours.update', $hour->id), [
        'user_id' => $this->volunteer->id,
        'hours' => 3,
        'primary_dept_id' => $this->department->id,
    ]);

    expect($hour->fresh()->counts_toward_perks)->toBeFalse();
});

it('lists the perk set in the dropdown when perks are configured', function () {
    configureAnActivePerk();

    $this->actingAs($this->admin)
        ->get(route('hours.create', $this->volunteer->id))
        ->assertOk()
        ->assertSee('perk_set_id', false)
        ->assertSee('Test Perk Set');
});

it('stores the selected perk set when one is chosen', function () {
    $perk = configureAnActivePerk();

    $this->actingAs($this->admin)->post(route('hours.store'), [
        'user_id' => $this->volunteer->id,
        'hours' => 2,
        'notes' => '',
        'volunteer_date' => now()->toDateString(),
        'primary_dept_id' => $this->department->id,
        'counts_toward_perks' => '1',
        'perk_set_id' => $perk->perk_set_id,
    ]);

    expect(VolunteerHours::first()->perk_set_id)->toBe($perk->perk_set_id);
});

it('defaults perk_set_id to null when no set is chosen', function () {
    configureAnActivePerk();

    $this->actingAs($this->admin)->post(route('hours.store'), [
        'user_id' => $this->volunteer->id,
        'hours' => 2,
        'notes' => '',
        'volunteer_date' => now()->toDateString(),
        'primary_dept_id' => $this->department->id,
        'counts_toward_perks' => '1',
    ]);

    expect(VolunteerHours::first()->perk_set_id)->toBeNull();
});

it('clears the perk set when the toggle is unchecked on update', function () {
    $perk = configureAnActivePerk();

    $hour = VolunteerHours::create([
        'user_id' => $this->volunteer->id,
        'hours' => 3,
        'primary_dept_id' => $this->department->id,
        'counts_toward_perks' => true,
        'perk_set_id' => $perk->perk_set_id,
        'fiscal_ledger_id' => FiscalLedger::first()->id,
    ]);

    $this->actingAs($this->admin)->patch(route('hours.update', $hour->id), [
        'user_id' => $this->volunteer->id,
        'hours' => 3,
        'primary_dept_id' => $this->department->id,
        // counts_toward_perks intentionally omitted, simulating an unchecked box
    ]);

    expect($hour->fresh())
        ->counts_toward_perks->toBeFalse()
        ->perk_set_id->toBeNull();
});

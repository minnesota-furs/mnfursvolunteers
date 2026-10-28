<?php

use App\Models\Election;
use App\Models\User;

it('lists active elections with a link to view each one', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $election = Election::create([
        'title' => 'Board Election',
        'description' => 'Vote!',
        'start_date' => now()->subDay(),
        'end_date' => now()->addWeek(),
        'active' => true,
        'max_positions' => 2,
        'min_voter_hours' => 0,
        'min_candidate_hours' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('elections.index'))
        ->assertOk()
        ->assertSee('Board Election')
        ->assertSee(route('elections.show', $election))
        ->assertSee('sm:flex-row', false);
});

it('shows an empty state when there are no active elections', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('elections.index'))
        ->assertOk()
        ->assertSee('No active elections');
});

it('shows only the description and current stage on the index', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $election = Election::create([
        'title' => 'Board Election',
        'description' => 'Vote for the board!',
        'start_date' => now()->addDays(3)->addHour(),
        'end_date' => now()->addWeeks(2),
        'active' => true,
        'max_positions' => 1,
        'min_voter_hours' => 10,
        'min_candidate_hours' => 0,
        'nomination_start_date' => now()->subDay(),
        'nomination_end_date' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->get(route('elections.index'))
        ->assertOk()
        ->assertSee('Vote for the board!')
        ->assertSee('Nominations Open')
        ->assertSee('Closes in 23 hours')
        ->assertSee('View Details')
        ->assertSee(route('elections.show', $election))
        ->assertDontSee('Voting Period')
        ->assertDontSee('Positions Available')
        ->assertDontSee('Volunteer Hours Requirements')
        ->assertDontSee('You still have time to report your hours')
        ->assertDontSee('Election progress');
});

it('reports the current stage of an election', function (array $attributes, string $label) {
    $election = Election::make(array_merge([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ], $attributes));

    expect($election->currentStage()['label'])->toBe($label);
})->with([
    'voting' => [['start_date' => now()->subDay()], 'Voting Open'],
    'nominations open' => [['nomination_start_date' => now()->subDay(), 'nomination_end_date' => now()->addDay()], 'Nominations Open'],
    'nominations soon' => [['nomination_start_date' => now()->addDay(), 'nomination_end_date' => now()->addDays(2)], 'Nominations Soon'],
    'nominations closed' => [['nomination_start_date' => now()->subDays(3), 'nomination_end_date' => now()->subDay()], 'Nominations Closed'],
    'upcoming' => [[], 'Upcoming'],
    'ended' => [['start_date' => now()->subDays(10), 'end_date' => now()->subDay()], 'Ended'],
]);

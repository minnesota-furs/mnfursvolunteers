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

function electionInReviewPeriod(): Election
{
    return Election::create([
        'title' => 'Review Election',
        'description' => 'Between nominations and voting.',
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subDay(),
        'start_date' => now()->addDays(3)->addHour(),
        'end_date' => now()->addWeeks(2),
        'active' => true,
        'max_positions' => 1,
        'min_voter_hours' => 0,
        'min_candidate_hours' => 0,
    ]);
}

it('lists elections that are in the review period between nominations and voting', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $election = electionInReviewPeriod();

    expect($election->isReviewPeriod())->toBeTrue();

    $this->actingAs($user)
        ->get(route('elections.index'))
        ->assertOk()
        ->assertSee('Review Election')
        ->assertSee('Nominations Closed')
        ->assertSee('Voting opens in 3 days')
        ->assertDontSee('No active elections');
});

it('lets users view an election during its review period', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $election = electionInReviewPeriod();

    $this->actingAs($user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Review Election');
});

it('does not treat elections before nominations or after voting as running', function () {
    $beforeNominations = Election::create([
        'title' => 'Future', 'description' => 'x', 'active' => true, 'max_positions' => 1,
        'nomination_start_date' => now()->addDay(), 'nomination_end_date' => now()->addDays(2),
        'start_date' => now()->addDays(5), 'end_date' => now()->addDays(9),
    ]);
    $finished = Election::create([
        'title' => 'Done', 'description' => 'x', 'active' => true, 'max_positions' => 1,
        'start_date' => now()->subWeeks(2), 'end_date' => now()->subWeek(),
    ]);

    expect(Election::currentlyRunning()->pluck('id')->all())->not->toContain($beforeNominations->id, $finished->id)
        ->and($beforeNominations->isReviewPeriod())->toBeFalse()
        ->and($finished->isReviewPeriod())->toBeFalse();
});

it('shows the Elections navigation link during the review period', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    electionInReviewPeriod();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('<p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Elections</p>', false);
});

it('hides the Elections navigation link when no election is running', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('<p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Elections</p>', false);
});

it('renders the nomination form with a compact submit button', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $election = Election::create([
        'title' => 'Board Election',
        'description' => 'Vote!',
        'nomination_start_date' => now()->subDay(),
        'nomination_end_date' => now()->addDay(),
        'start_date' => now()->addDays(3),
        'end_date' => now()->addWeeks(2),
        'active' => true,
        'max_positions' => 1,
        'allow_self_nomination' => true,
        'min_voter_hours' => 0,
        'min_candidate_hours' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('elections.nominate', $election))
        ->assertOk()
        ->assertSee('Submit Nomination')
        ->assertSee('px-3 py-2 text-sm font-semibold text-white', false)
        ->assertDontSee('px-6 py-3 text-base', false);
});

it('shows a manage elections button only to users who can manage elections', function () {
    $manager = User::factory()->create(['onboarded_at' => now()]);
    $manager->givePermission('Manage Elections');
    $manageUrl = route('admin.elections.index');

    $this->actingAs($manager)
        ->get(route('elections.index'))
        ->assertOk()
        ->assertSee('Manage Elections')
        ->assertSee('href="'.$manageUrl.'"', false);

    $this->actingAs(User::factory()->create(['onboarded_at' => now()]))
        ->get(route('elections.index'))
        ->assertOk()
        ->assertDontSee('href="'.$manageUrl.'"', false);
});

it('shows the timeline on the public election page', function () {
    User::factory()->create();
    $election = electionInReviewPeriod();

    $this->get(route('elections-public.show', $election))
        ->assertOk()
        ->assertSee('Election progress: currently in Review.')
        ->assertSeeInOrder(['Nominations', 'Review', 'Voting', 'Election Results']);
});

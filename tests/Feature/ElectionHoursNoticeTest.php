<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\FiscalLedger;
use App\Models\User;
use App\Models\VolunteerHours;
use Illuminate\Support\Facades\DB;

function makeElection(array $attributes = []): Election
{
    $election = Election::create(array_merge([
        'title' => 'Board Election',
        'description' => 'Vote!',
        'start_date' => now()->subDay(),
        'end_date' => now()->addWeek(),
        'active' => true,
        'max_positions' => 1,
        'min_voter_hours' => 10,
        'min_candidate_hours' => 0,
    ], $attributes));

    Candidate::create([
        'election_id' => $election->id,
        'user_id' => User::factory()->create()->id,
        'statement' => 'Pick me',
        'approved' => true,
        'withdrawn' => false,
    ]);

    return $election;
}

beforeEach(function () {
    $this->user = User::factory()->create(['onboarded_at' => now()]);
});

it('shows the report hours notice with time remaining when a ledger is attached', function () {
    $ledger = FiscalLedger::factory()->create([
        'name' => 'FY Test',
        'start_date' => now()->subMonths(6),
        'end_date' => now()->addDays(10),
    ]);
    $election = makeElection(['fiscal_ledger_id' => $ledger->id]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('You still have time to report your hours')
        ->assertSee('FY Test period ends')
        ->assertSee('Voting: 10 more hours')
        ->assertDontSee('Self-nominating')
        ->assertSee(route('hours.create', $this->user->id))
        ->assertSee('Log Hours');
});

it('shows the time left in the current fiscal year when no ledger is attached', function () {
    FiscalLedger::factory()->create([
        'name' => 'FY Current',
        'start_date' => now()->subMonths(6),
        'end_date' => now()->addDays(20),
    ]);
    $election = makeElection();

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('You still have time to report your hours')
        ->assertSee('FY Current period ends');
});

it('shows the notice without a countdown when no fiscal year is current', function () {
    $election = makeElection();

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('You still have time to report your hours')
        ->assertDontSee('period ends');
});

it('hides the notice when the ledger period has ended', function () {
    $ledger = FiscalLedger::factory()->create([
        'start_date' => now()->subYear(),
        'end_date' => now()->subDay(),
    ]);
    $election = makeElection(['fiscal_ledger_id' => $ledger->id]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertDontSee('You still have time to report your hours');
});

it('hides the notice when the user has enough hours', function () {
    $ledger = FiscalLedger::factory()->create([
        'start_date' => now()->subMonths(6),
        'end_date' => now()->addDays(10),
    ]);
    $election = makeElection(['fiscal_ledger_id' => $ledger->id]);
    VolunteerHours::factory()->create([
        'user_id' => $this->user->id,
        'fiscal_ledger_id' => $ledger->id,
        'hours' => 12,
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertDontSee('You still have time to report your hours');
});

it('hides the notice when the election has no hours requirement', function () {
    $election = makeElection(['min_voter_hours' => 0]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertDontSee('You still have time to report your hours');
});

it('breaks down voting and self-nomination hours needed', function () {
    $election = makeElection(['min_voter_hours' => 5, 'min_candidate_hours' => 20, 'allow_self_nomination' => true]);
    VolunteerHours::factory()->create([
        'user_id' => $this->user->id,
        'fiscal_ledger_id' => FiscalLedger::factory()->create([
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addDays(10),
        ])->id,
        'hours' => 8,
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Self-nominating: 12 more hours')
        ->assertDontSee('Voting:');
});

it('shows only the voting breakdown when self-nomination is disabled', function () {
    $election = makeElection(['min_candidate_hours' => 20, 'allow_self_nomination' => false]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Voting: 10 more hours')
        ->assertDontSee('Self-nominating');
});

it('hides the self-nomination breakdown once the nomination period has closed', function () {
    $election = makeElection([
        'min_candidate_hours' => 20,
        'allow_self_nomination' => true,
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subWeek(),
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Voting: 10 more hours')
        ->assertDontSee('Self-nominating');
});

it('shows a segmented timeline of the election periods', function () {
    $election = makeElection([
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subWeek(),
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('role="img"', false)
        ->assertSee('Election progress: currently in')
        ->assertSeeInOrder(['Nominations', 'Review', 'Voting', 'Election Results'])
        ->assertSee('Now');
});

it('shows only the voting segment when there is no nomination period', function () {
    $election = makeElection();

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('role="img"', false)
        ->assertSee('Election progress: currently in')
        ->assertSee('Election Results')
        ->assertSee($election->end_date->format('M j'))
        ->assertDontSee('Review');
});

it('fills timeline segments up to the current time', function () {
    $election = makeElection([
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subWeek(),
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('style="width: 100%"', false)
        ->assertSee('style="width: 0%"', false);
});

it('words the voting open status as a closing countdown', function () {
    $election = makeElection(['end_date' => now()->addMonth()->addDay()]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Voting Open')
        ->assertSee('Closes in 1 month')
        ->assertDontSee('from now');
});

it('describes the timeline for assistive tech by current phase', function () {
    $election = makeElection([
        'start_date' => now()->addDay(),
        'end_date' => now()->addWeek(),
        'nomination_start_date' => now()->subDay(),
        'nomination_end_date' => now()->addHours(12),
    ]);

    expect($election->timeline()['currentLabel'])->toBe('Nominations');

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Election progress: currently in Nominations.');
});

it('looks up a user\'s hours once per election instance', function () {
    $election = makeElection();
    $user = $this->user;

    DB::enableQueryLog();
    $election->userCanVote($user);
    $election->userCanBeCandidate($user);
    $election->userVoterHoursShortfall($user);
    $election->userCandidateHoursShortfall($user);
    $queryCount = count(DB::getQueryLog());
    DB::flushQueryLog();

    $election->userCanVote($user);

    expect(DB::getQueryLog())->toBeEmpty();
    expect($queryCount)->toBeGreaterThan(0);
});

it('places the now marker and fills elapsed segments in the timeline data', function () {
    $election = makeElection([
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subWeek(),
    ]);

    $timeline = $election->timeline();

    expect(collect($timeline['segments'])->pluck('label')->all())->toBe(['Nominations', 'Review', 'Voting', 'Election Results'])
        ->and($timeline['segments'][0]['fill'])->toEqual(100)
        ->and($timeline['segments'][3]['fill'])->toEqual(0)
        ->and($timeline['currentLabel'])->toBe('Voting')
        ->and($timeline['nowFraction'])->toBeGreaterThan(0)->toBeLessThan(1);
});

it('warns nominated users short on hours they may be removed from the ballot', function () {
    $election = makeElection(['min_candidate_hours' => 20, 'allow_self_nomination' => true]);
    Candidate::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'Me',
        'approved' => false,
        'withdrawn' => false,
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Candidate: 20 more hours')
        ->assertSee('you may be removed from the ballot after the nomination period ends');
});

it('shows the nominated warning even when self-nomination is disabled', function () {
    $election = makeElection(['min_candidate_hours' => 20, 'allow_self_nomination' => false, 'min_voter_hours' => 0]);
    Candidate::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'Me',
        'approved' => true,
        'withdrawn' => false,
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('removed from the ballot');
});

it('does not warn users who are not nominated or whose nomination was withdrawn', function () {
    $election = makeElection(['min_candidate_hours' => 20, 'allow_self_nomination' => true]);
    Candidate::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'Me',
        'approved' => true,
        'withdrawn' => true,
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('Self-nominating: 20 more hours')
        ->assertDontSee('removed from the ballot');
});

it('does not warn nominated users once the nomination period has closed', function () {
    $election = makeElection([
        'min_candidate_hours' => 20,
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subWeek(),
    ]);
    Candidate::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'Me',
        'approved' => true,
        'withdrawn' => false,
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertDontSee('removed from the ballot');
});

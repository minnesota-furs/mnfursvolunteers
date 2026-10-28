<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;

function electionWithCandidates(User $candidateUser, array $attributes = []): Election
{
    $election = Election::create(array_merge([
        'title' => 'Board Election',
        'description' => 'Vote!',
        'nomination_start_date' => now()->subDay(),
        'nomination_end_date' => now()->addDay(),
        'start_date' => now()->addDays(3),
        'end_date' => now()->addWeeks(2),
        'active' => true,
        'max_positions' => 1,
        'min_voter_hours' => 0,
        'min_candidate_hours' => 0,
    ], $attributes));

    foreach ([$candidateUser, User::factory()->create()] as $user) {
        Candidate::create([
            'election_id' => $election->id,
            'user_id' => $user->id,
            'statement' => 'Pick me',
            'approved' => true,
            'withdrawn' => false,
        ]);
    }

    return $election;
}

beforeEach(function () {
    $this->user = User::factory()->create(['onboarded_at' => now()]);
});

it('highlights the current user\'s candidacy during nominations with removal guidance', function () {
    $election = electionWithCandidates($this->user);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertSee('bg-blue-50 dark:bg-blue-900/20 border-blue-300', false)
        ->assertSee('Want to remove yourself as a candidate? Please reach out to the elections team within the org.');
});

it('shows the removal guidance only once, for the current user\'s own card', function () {
    $election = electionWithCandidates($this->user);

    $html = $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->getContent();

    expect(substr_count($html, 'reach out to the elections team'))->toBe(1);
});

it('does not highlight anyone for users who are not candidates', function () {
    $election = electionWithCandidates(User::factory()->create());

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertDontSee('reach out to the elections team');
});

it('does not show the removal guidance once voting has started', function () {
    $election = electionWithCandidates($this->user, [
        'start_date' => now()->subHour(),
        'end_date' => now()->addWeek(),
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertDontSee('reach out to the elections team');
});

it('does not show the removal guidance after nominations have closed', function () {
    $election = electionWithCandidates($this->user, [
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subWeek(),
        'start_date' => now()->subHour(),
        'end_date' => now()->addWeek(),
    ]);

    $this->actingAs($this->user)
        ->get(route('elections.show', $election))
        ->assertOk()
        ->assertDontSee('reach out to the elections team');
});

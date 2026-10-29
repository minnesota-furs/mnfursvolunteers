<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['onboarded_at' => now()]);
    $this->admin->givePermission('Manage Elections');
});

it('lists elections with candidate counts and the real stage', function () {
    $election = Election::factory()->create([
        'title' => 'Board Election',
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subDay(),
        'start_date' => now()->addDays(3),
        'end_date' => now()->addWeeks(2),
    ]);
    Candidate::create(['election_id' => $election->id, 'user_id' => User::factory()->create()->id, 'statement' => 'x', 'approved' => true, 'withdrawn' => false]);

    $this->actingAs($this->admin)
        ->get(route('admin.elections.index'))
        ->assertOk()
        ->assertSee('Board Election')
        ->assertSee('Nominations Closed')
        ->assertDontSee('Upcoming');
});

it('marks inactive elections as inactive rather than by date', function () {
    Election::factory()->create(['title' => 'Paused Election', 'active' => false]);

    $this->actingAs($this->admin)
        ->get(route('admin.elections.index'))
        ->assertOk()
        ->assertSee('Inactive')
        ->assertDontSee('Voting Open');
});

it('keeps the table scrollable and evenly padded on the sides', function () {
    Election::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.elections.index'))
        ->assertOk()
        ->assertSee('overflow-x-auto', false)
        ->assertDontSee('sm:pl-0', false)
        ->assertDontSee('sm:pr-0', false);
});

it('shows the vote count only after voting ends', function () {
    Election::factory()->create(['start_date' => now()->subWeeks(2), 'end_date' => now()->subDay()]);

    $this->actingAs($this->admin)
        ->get(route('admin.elections.index'))
        ->assertOk()
        ->assertDontSee('Pending')
        ->assertSee('Ended');
});

it('shows the timeline on the admin election page', function () {
    $election = Election::factory()->create([
        'nomination_start_date' => now()->subWeeks(2),
        'nomination_end_date' => now()->subWeek(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.elections.show', $election))
        ->assertOk()
        ->assertSee('Election progress: currently in Voting.')
        ->assertSeeInOrder(['Nominations', 'Review', 'Voting', 'Election Results']);
});

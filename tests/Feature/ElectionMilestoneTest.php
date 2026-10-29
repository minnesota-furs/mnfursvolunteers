<?php

use App\Models\Election;
use App\Models\ElectionMilestone;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['onboarded_at' => now()]);
    $this->admin->givePermission('Manage Elections');
    $this->election = Election::factory()->create();
});

it('lets an admin add a milestone with an optional location', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.elections.milestones.store', $this->election), [
            'title' => 'Q&A Debate',
            'description' => 'Ask the candidates anything.',
            'location' => 'Main Hall',
            'starts_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ])
        ->assertRedirect(route('admin.elections.milestones.index', $this->election));

    $milestone = $this->election->milestones()->first();

    expect($milestone->title)->toBe('Q&A Debate')
        ->and($milestone->location)->toBe('Main Hall')
        ->and($milestone->description)->toBe('Ask the candidates anything.');
});

it('allows a milestone without a description or location', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.elections.milestones.store', $this->election), [
            'title' => 'Candidate Bios Due',
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])
        ->assertSessionHasNoErrors();

    expect($this->election->milestones()->first())
        ->description->toBeNull()
        ->location->toBeNull();
});

it('validates milestone input', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.elections.milestones.store', $this->election), $payload)
        ->assertSessionHasErrors($field);

    expect($this->election->milestones()->count())->toBe(0);
})->with([
    'missing title' => [['starts_at' => '2030-01-01T10:00'], 'title'],
    'missing date' => [['title' => 'Debate'], 'starts_at'],
    'invalid date' => [['title' => 'Debate', 'starts_at' => 'not a date'], 'starts_at'],
    'title too long' => [['title' => str_repeat('a', 256), 'starts_at' => '2030-01-01T10:00'], 'title'],
    'location too long' => [['title' => 'Debate', 'starts_at' => '2030-01-01T10:00', 'location' => str_repeat('a', 256)], 'location'],
]);

it('lets an admin update and remove a milestone', function () {
    $milestone = ElectionMilestone::factory()->for($this->election)->create(['title' => 'Old']);

    $this->actingAs($this->admin)
        ->put(route('admin.elections.milestones.update', [$this->election, $milestone]), [
            'title' => 'New',
            'starts_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'location' => 'Online',
        ])
        ->assertRedirect(route('admin.elections.milestones.index', $this->election));

    expect($milestone->fresh())->title->toBe('New')->location->toBe('Online');

    $this->actingAs($this->admin)
        ->delete(route('admin.elections.milestones.destroy', [$this->election, $milestone]))
        ->assertRedirect();

    expect(ElectionMilestone::count())->toBe(0);
});

it('does not let a milestone be changed through a different election', function () {
    $otherElection = Election::factory()->create();
    $milestone = ElectionMilestone::factory()->for($otherElection)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.elections.milestones.destroy', [$this->election, $milestone]))
        ->assertNotFound();

    expect($milestone->fresh())->not->toBeNull();
});

it('keeps non-admins out of milestone management', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('admin.elections.milestones.index', $this->election))
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('admin.elections.milestones.store', $this->election), [
            'title' => 'Sneaky',
            'starts_at' => '2030-01-01T10:00',
        ])
        ->assertForbidden();

    expect(ElectionMilestone::count())->toBe(0);
});

it('shows the milestone admin page with existing milestones', function () {
    ElectionMilestone::factory()->for($this->election)->create(['title' => 'Candidate Bios Due']);

    $this->actingAs($this->admin)
        ->get(route('admin.elections.milestones.index', $this->election))
        ->assertOk()
        ->assertSee('Candidate Bios Due');
});

it('shows milestones as key dates on the election page, in date order', function () {
    ElectionMilestone::factory()->for($this->election)->withLocation('Main Hall')->create([
        'title' => 'Q&A Debate',
        'description' => 'Bring questions.',
        'starts_at' => now()->addDays(5),
    ]);
    ElectionMilestone::factory()->for($this->election)->create([
        'title' => 'Candidate Bios Due',
        'description' => null,
        'starts_at' => now()->addDays(2),
    ]);

    $this->actingAs(User::factory()->create(['onboarded_at' => now()]))
        ->get(route('elections.show', $this->election))
        ->assertOk()
        ->assertSee('Key Dates (2)')
        ->assertSee('<details class="group mb-6">', false)
        ->assertSeeInOrder(['Candidate Bios Due', 'Q&amp;A Debate', 'Main Hall', 'Bring questions.'], false);
});

it('hides the key dates section when an election has no milestones', function () {
    $this->actingAs(User::factory()->create(['onboarded_at' => now()]))
        ->get(route('elections.show', $this->election))
        ->assertOk()
        ->assertDontSee('Key Dates');
});

it('removes milestones when their election is deleted', function () {
    ElectionMilestone::factory()->for($this->election)->create();

    $this->election->delete();

    expect(ElectionMilestone::count())->toBe(0);
});

it('renders milestone descriptions as markdown', function () {
    ElectionMilestone::factory()->for($this->election)->create([
        'description' => "**Bring questions** and see the [agenda](https://example.com/agenda).\n\n- Intros\n- Q&A",
    ]);

    $this->actingAs(User::factory()->create(['onboarded_at' => now()]))
        ->get(route('elections.show', $this->election))
        ->assertOk()
        ->assertSee('<strong>Bring questions</strong>', false)
        ->assertSee('<a href="https://example.com/agenda">agenda</a>', false)
        ->assertSee('<li>Intros</li>', false);
});

it('escapes raw html in milestone descriptions', function () {
    $milestone = ElectionMilestone::factory()->for($this->election)->create([
        'description' => '<script>alert(1)</script> hello',
    ]);

    expect((string) $milestone->descriptionHtml())->not->toContain('<script>')
        ->and((string) $milestone->descriptionHtml())->toContain('&lt;script&gt;');

    $this->actingAs(User::factory()->create(['onboarded_at' => now()]))
        ->get(route('elections.show', $this->election))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('has no rendered description when none is set', function () {
    $milestone = ElectionMilestone::factory()->for($this->election)->create(['description' => null]);

    expect($milestone->descriptionHtml())->toBeNull();
});

it('badges only past key dates as past', function () {
    ElectionMilestone::factory()->for($this->election)->create(['title' => 'Already Happened', 'starts_at' => now()->subDay()]);
    ElectionMilestone::factory()->for($this->election)->create(['title' => 'Coming Up', 'starts_at' => now()->addDay()]);

    $html = $this->actingAs(User::factory()->create(['onboarded_at' => now()]))
        ->get(route('elections.show', $this->election))
        ->assertOk()
        ->getContent();

    expect(substr_count($html, '>Past</span>'))->toBe(1);
});

it('shows an edit button on the election page only to users who can manage elections', function () {
    $editUrl = route('admin.elections.edit', $this->election);

    $this->actingAs($this->admin)
        ->get(route('elections.show', $this->election))
        ->assertOk()
        ->assertSee('href="'.$editUrl.'"', false);

    $this->actingAs(User::factory()->create(['onboarded_at' => now()]))
        ->get(route('elections.show', $this->election))
        ->assertOk()
        ->assertDontSee($editUrl);
});

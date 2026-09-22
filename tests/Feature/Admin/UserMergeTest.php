<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Shift;
use App\Models\Tag;
use App\Models\User;
use App\Models\VolunteerHours;
use App\Models\Vote;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $this->admin->givePermission('Manage Users');
});

it('forbids users without manage-users permission from merging', function () {
    $viewer = User::factory()->create(['onboarded_at' => now()]);
    $survivor = User::factory()->create(['onboarded_at' => now()]);
    $duplicate = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($viewer)
        ->post(route('admin.users.merge'), [
            'user_ids' => "{$survivor->id},{$duplicate->id}",
            'primary_user_id' => $survivor->id,
        ])
        ->assertForbidden();
});

it('rejects fewer than two selected users', function () {
    $survivor = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($this->admin)
        ->post(route('admin.users.merge'), [
            'user_ids' => "{$survivor->id}",
            'primary_user_id' => $survivor->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('rejects a primary_user_id that is not one of the selected users', function () {
    $survivor = User::factory()->create(['onboarded_at' => now()]);
    $duplicate = User::factory()->create(['onboarded_at' => now()]);
    $outsider = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($this->admin)
        ->post(route('admin.users.merge'), [
            'user_ids' => "{$survivor->id},{$duplicate->id}",
            'primary_user_id' => $outsider->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($duplicate->fresh()->trashed())->toBeFalse();
});

it('moves hours, tags, and shift signups onto the survivor and trashes the duplicate', function () {
    $survivor = User::factory()->create(['onboarded_at' => now()]);
    $duplicate = User::factory()->create(['onboarded_at' => now()]);

    VolunteerHours::factory()->create(['user_id' => $survivor->id, 'hours' => 3]);
    VolunteerHours::factory()->create(['user_id' => $duplicate->id, 'hours' => 5]);

    $tagOnSurvivor = Tag::create(['name' => 'Registration']);
    $tagOnDuplicate = Tag::create(['name' => 'Setup Crew']);
    $survivor->tags()->attach($tagOnSurvivor);
    $duplicate->tags()->attach($tagOnDuplicate);

    $shiftA = Shift::factory()->create();
    $shiftB = Shift::factory()->create();
    $survivor->shifts()->attach($shiftA);
    $duplicate->shifts()->attach($shiftB);

    $this->actingAs($this->admin)
        ->post(route('admin.users.merge'), [
            'user_ids' => "{$survivor->id},{$duplicate->id}",
            'primary_user_id' => $survivor->id,
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $survivor = $survivor->fresh(['volunteerHours', 'tags', 'shifts']);

    expect($survivor->totalVolunteerHours())->toBe(8.0)
        ->and($survivor->tags->pluck('id')->sort()->values()->all())->toBe([$tagOnSurvivor->id, $tagOnDuplicate->id])
        ->and($survivor->shifts->pluck('id')->sort()->values()->all())->toBe([$shiftA->id, $shiftB->id]);

    $duplicate = $duplicate->fresh();
    expect($duplicate->trashed())->toBeTrue()
        ->and($duplicate->merged_into_id)->toBe($survivor->id);
});

it('resolves collisions on unique constraints instead of erroring', function () {
    $survivor = User::factory()->create(['onboarded_at' => now()]);
    $duplicate = User::factory()->create(['onboarded_at' => now()]);

    $sharedTag = Tag::create(['name' => 'Shared Tag']);
    $survivor->tags()->attach($sharedTag);
    $duplicate->tags()->attach($sharedTag);

    $sharedShift = Shift::factory()->create();
    $survivor->shifts()->attach($sharedShift);
    $duplicate->shifts()->attach($sharedShift);

    $election = Election::create([
        'title' => 'Board Election',
        'start_date' => now(),
        'end_date' => now()->addWeek(),
    ]);
    $candidate = Candidate::create(['election_id' => $election->id, 'user_id' => $survivor->id]);
    Vote::create(['election_id' => $election->id, 'user_id' => $survivor->id, 'candidate_id' => $candidate->id]);
    Vote::create(['election_id' => $election->id, 'user_id' => $duplicate->id, 'candidate_id' => $candidate->id]);

    $this->actingAs($this->admin)
        ->post(route('admin.users.merge'), [
            'user_ids' => "{$survivor->id},{$duplicate->id}",
            'primary_user_id' => $survivor->id,
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $survivor = $survivor->fresh(['tags', 'shifts']);

    expect($survivor->tags->pluck('id')->all())->toBe([$sharedTag->id])
        ->and($survivor->shifts->pluck('id')->all())->toBe([$sharedShift->id])
        ->and(Vote::where('election_id', $election->id)->where('candidate_id', $candidate->id)->count())->toBe(1)
        ->and(Vote::where('user_id', $survivor->id)->exists())->toBeTrue();
});

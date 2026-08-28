<?php

use App\Models\ConcatSectorRoleMapping;
use App\Models\ConcatUserRoleGrant;
use App\Models\Sector;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $this->admin->givePermission('Manage Users');
});

it('shows not checked yet when the user has never been looked up in ConCat', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($this->admin)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee('ConCat')
        ->assertSee('Not checked yet');
});

it('shows not associated when a lookup found no matching ConCat account', function () {
    $user = User::factory()->create(['onboarded_at' => now(), 'concat_checked_at' => now()]);

    $this->actingAs($this->admin)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee('Not associated');
});

it('shows the associated status and granted roles when the user is linked', function () {
    $sector = Sector::factory()->create(['name' => 'Operations']);
    ConcatSectorRoleMapping::create([
        'sector_id' => $sector->id,
        'concat_role_id' => 'role-123',
        'concat_role_name' => 'Staff',
        'concat_scope' => 'convention',
    ]);

    $user = User::factory()->create([
        'onboarded_at' => now(),
        'concat_user_id' => 'concat-user-1',
        'concat_checked_at' => now(),
    ]);
    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $sector->id,
        'concat_user_id' => 'concat-user-1',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee('Associated with a ConCat account')
        ->assertSee('Staff')
        ->assertSee('Operations');
});

it('hides ConCat status from users who cannot view sensitive info', function () {
    $viewer = User::factory()->create(['onboarded_at' => now()]);
    $user = User::factory()->create(['onboarded_at' => now(), 'concat_user_id' => 'concat-user-1']);

    $this->actingAs($viewer)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertDontSee('ConCat Status');
});

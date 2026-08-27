<?php

use App\Models\ConcatSectorRoleMapping;
use App\Models\ConcatUserRoleGrant;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use App\Services\ConcatService;
use App\Services\ConcatSyncService;

beforeEach(function () {
    $this->sector = Sector::factory()->create(['name' => 'Operations']);
    $this->departmentA = Department::factory()->create(['sector_id' => $this->sector->id]);
    $this->departmentB = Department::factory()->create(['sector_id' => $this->sector->id]);

    $this->mapping = ConcatSectorRoleMapping::create([
        'sector_id' => $this->sector->id,
        'concat_role_id' => 'role-123',
        'concat_role_name' => 'Staff',
        'concat_scope' => 'convention',
    ]);
});

it('grants a role to a newly assigned department member with a matching ConCat email', function () {
    $user = User::factory()->create(['email' => 'volunteer@example.com']);
    $this->departmentA->users()->attach($user);

    $concat = $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('findUserByEmail')->once()
            ->with('volunteer@example.com')
            ->andReturn(['id' => 'concat-user-1', 'email' => 'volunteer@example.com']);
        $mock->shouldReceive('grantRole')->once()
            ->with('concat-user-1', 'role-123', 'convention')
            ->andReturn(true);
    });

    $result = (new ConcatSyncService($concat))->syncSector($this->sector, $this->mapping);

    expect($result)->toBe(['granted' => 1, 'revoked' => 0, 'unmatched' => 0]);
    expect(ConcatUserRoleGrant::where('user_id', $user->id)->where('sector_id', $this->sector->id)->exists())->toBeTrue();
    expect($user->fresh()->concat_user_id)->toBe('concat-user-1');
});

it('does not grant a role when no matching ConCat user is found', function () {
    $user = User::factory()->create(['email' => 'nobody@example.com']);
    $this->departmentA->users()->attach($user);

    $concat = $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('findUserByEmail')->once()->andReturn(null);
        $mock->shouldNotReceive('grantRole');
    });

    $result = (new ConcatSyncService($concat))->syncSector($this->sector, $this->mapping);

    expect($result)->toBe(['granted' => 0, 'revoked' => 0, 'unmatched' => 1]);
    expect(ConcatUserRoleGrant::count())->toBe(0);
});

it('revokes the role when a user leaves every department in the sector', function () {
    $user = User::factory()->create();
    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $this->sector->id,
        'concat_user_id' => 'concat-user-1',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $concat = $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('revokeRole')->once()->with('concat-user-1', 'role-123')->andReturn(true);
    });

    $result = (new ConcatSyncService($concat))->syncSector($this->sector, $this->mapping);

    expect($result)->toBe(['granted' => 0, 'revoked' => 1, 'unmatched' => 0]);
    expect(ConcatUserRoleGrant::where('user_id', $user->id)->exists())->toBeFalse();
});

it('does nothing when a user moves between two departments within the same watched sector', function () {
    $user = User::factory()->create();
    $this->departmentA->users()->attach($user);

    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $this->sector->id,
        'concat_user_id' => 'concat-user-1',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $this->departmentA->users()->detach($user);
    $this->departmentB->users()->attach($user);

    $concat = $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldNotReceive('grantRole');
        $mock->shouldNotReceive('revokeRole');
    });

    $result = (new ConcatSyncService($concat))->syncSector($this->sector, $this->mapping);

    expect($result)->toBe(['granted' => 0, 'revoked' => 0, 'unmatched' => 0]);
    expect(ConcatUserRoleGrant::where('user_id', $user->id)->where('sector_id', $this->sector->id)->exists())->toBeTrue();
});

it('deletes the local grant even when ConCat refuses to revoke it', function () {
    $user = User::factory()->create();
    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $this->sector->id,
        'concat_user_id' => 'concat-user-1',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $concat = $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('revokeRole')->once()->andReturn(false);
    });

    (new ConcatSyncService($concat))->syncSector($this->sector, $this->mapping);

    expect(ConcatUserRoleGrant::where('user_id', $user->id)->exists())->toBeFalse();
});

it('associateUser revokes old grants and regrants under the new ConCat account', function () {
    $user = User::factory()->create();
    $this->departmentA->users()->attach($user);

    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $this->sector->id,
        'concat_user_id' => 'wrong-concat-id',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $concat = $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('revokeRole')->once()->with('wrong-concat-id', 'role-123')->andReturn(true);
        $mock->shouldReceive('grantRole')->once()->with('correct-concat-id', 'role-123', 'convention')->andReturn(true);
        $mock->shouldNotReceive('findUserByEmail');
    });

    $result = (new ConcatSyncService($concat))->associateUser($user, 'correct-concat-id');

    expect($result)->toBe(['granted' => 1, 'revoked' => 1]);
    expect($user->fresh()->concat_user_id)->toBe('correct-concat-id');
    expect(ConcatUserRoleGrant::where('user_id', $user->id)->where('concat_user_id', 'correct-concat-id')->exists())->toBeTrue();
});

it('disassociateUser clears the link and revokes every grant', function () {
    $user = User::factory()->create(['concat_user_id' => 'concat-user-1', 'concat_checked_at' => now()]);
    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $this->sector->id,
        'concat_user_id' => 'concat-user-1',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $concat = $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('revokeRole')->once()->with('concat-user-1', 'role-123')->andReturn(true);
    });

    $result = (new ConcatSyncService($concat))->disassociateUser($user);

    expect($result)->toBe(['revoked' => 1]);
    expect($user->fresh()->concat_user_id)->toBeNull();
    expect($user->fresh()->concat_checked_at)->toBeNull();
    expect(ConcatUserRoleGrant::where('user_id', $user->id)->exists())->toBeFalse();
});

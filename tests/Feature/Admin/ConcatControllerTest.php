<?php

use App\Models\ApplicationSetting;
use App\Models\ConcatSectorRoleMapping;
use App\Models\ConcatUserRoleGrant;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use App\Services\ConcatService;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
    $this->volunteer = User::factory()->create(['onboarded_at' => now()]);

    ApplicationSetting::set('concat_api_base_url', 'https://fm-test.concat.app', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_secret', 'super-secret', 'encrypted', null, 'integrations');
});

it('forbids non-admins from viewing the manage concat page', function () {
    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
    });

    $this->actingAs($this->volunteer)
        ->get(route('admin.concat.index'))
        ->assertForbidden();
});

it('redirects to settings when ConCat is not connected', function () {
    ApplicationSetting::where('key', 'concat_client_id')->delete();
    ApplicationSetting::clearCache();

    $this->actingAs($this->admin)
        ->get(route('admin.concat.index'))
        ->assertRedirect(route('settings.index'));
});

it('shows the sector mapping table when connected', function () {
    Sector::factory()->create(['name' => 'Operations']);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getRoles')->andReturn([
            ['id' => 'role-123', 'name' => 'Staff'],
        ]);
    });

    $this->actingAs($this->admin)
        ->get(route('admin.concat.index'))
        ->assertOk()
        ->assertSee('Operations')
        ->assertSee('Staff');
});

it('creates a sector mapping and syncs it immediately when saved', function () {
    $sector = Sector::factory()->create();
    $department = Department::factory()->create(['sector_id' => $sector->id]);
    $matchedUser = User::factory()->create(['email' => 'match@example.com']);
    $department->users()->attach($matchedUser);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getRoles')->andReturn([
            ['id' => 'role-123', 'name' => 'Staff'],
        ]);
        $mock->shouldReceive('findUserByEmail')->once()->with('match@example.com')
            ->andReturn(['id' => 'concat-user-1']);
        $mock->shouldReceive('grantRole')->once()->with('concat-user-1', 'role-123', 'convention')->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->put(route('admin.concat.update'), [
            'sectors' => [
                $sector->id => [
                    'watched' => '1',
                    'concat_role_id' => 'role-123',
                    'concat_scope' => 'convention',
                ],
            ],
        ])
        ->assertRedirect(route('admin.concat.index'));

    expect(ConcatSectorRoleMapping::where('sector_id', $sector->id)->exists())->toBeTrue();
    expect(ConcatUserRoleGrant::where('user_id', $matchedUser->id)->exists())->toBeTrue();
});

it('revokes grants and removes the mapping when a sector is unwatched', function () {
    $sector = Sector::factory()->create();
    ConcatSectorRoleMapping::create([
        'sector_id' => $sector->id,
        'concat_role_id' => 'role-123',
        'concat_role_name' => 'Staff',
        'concat_scope' => 'convention',
    ]);
    $user = User::factory()->create();
    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $sector->id,
        'concat_user_id' => 'concat-user-1',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getRoles')->andReturn([['id' => 'role-123', 'name' => 'Staff']]);
        $mock->shouldReceive('revokeRole')->once()->with('concat-user-1', 'role-123')->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->put(route('admin.concat.update'), [
            'sectors' => [
                $sector->id => [
                    'watched' => '0',
                ],
            ],
        ])
        ->assertRedirect(route('admin.concat.index'));

    expect(ConcatSectorRoleMapping::where('sector_id', $sector->id)->exists())->toBeFalse();
    expect(ConcatUserRoleGrant::where('sector_id', $sector->id)->exists())->toBeFalse();
});

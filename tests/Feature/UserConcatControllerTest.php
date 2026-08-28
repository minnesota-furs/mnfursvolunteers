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
    $this->admin->givePermission('Manage Users');

    ApplicationSetting::set('concat_api_base_url', 'https://fm-test.concat.app', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_secret', 'super-secret', 'encrypted', null, 'integrations');
});

it('renders the ConCat account card on the user edit page', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($this->admin)
        ->get(route('users.edit', $user))
        ->assertOk()
        ->assertSee('ConCat Account')
        ->assertSee('Search ConCat by a different email')
        ->assertSee('Link by ConCat User ID');
});

it('tells the admin to connect ConCat first when it is not configured', function () {
    ApplicationSetting::where('key', 'like', 'concat_%')->delete();
    ApplicationSetting::clearCache();

    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($this->admin)
        ->get(route('users.edit', $user))
        ->assertOk()
        ->assertSee('ConCat is not connected');
});

it('links a user by searching a different email and re-syncs their watched sectors', function () {
    $sector = Sector::factory()->create(['name' => 'Operations']);
    Department::factory()->create(['sector_id' => $sector->id])
        ->users()->attach($user = User::factory()->create(['email' => 'primary@example.com', 'onboarded_at' => now()]));
    ConcatSectorRoleMapping::create([
        'sector_id' => $sector->id,
        'concat_role_id' => 'role-123',
        'concat_role_name' => 'Staff',
        'concat_scope' => 'convention',
    ]);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('findUserByEmail')->once()->with('other@example.com')
            ->andReturn(['id' => 'concat-user-1', 'firstName' => 'Jane', 'lastName' => 'Doe', 'email' => 'other@example.com']);
        $mock->shouldReceive('grantRole')->once()->with('concat-user-1', 'role-123', 'convention')->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->post(route('users.concat.search', $user), ['concat_search_email' => 'other@example.com'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->concat_user_id)->toBe('concat-user-1');
    expect(ConcatUserRoleGrant::where('user_id', $user->id)->where('concat_role_id', 'role-123')->exists())->toBeTrue();
});

it('shows an error when the searched email has no ConCat match', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('findUserByEmail')->once()->andReturn(null);
    });

    $this->actingAs($this->admin)
        ->post(route('users.concat.search', $user), ['concat_search_email' => 'nobody@example.com'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($user->fresh()->concat_user_id)->toBeNull();
});

it('links a user directly by ConCat user ID', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getUserById')->once()->with('4821')
            ->andReturn(['id' => '4821', 'firstName' => 'Jane', 'lastName' => 'Doe', 'email' => 'jane@example.com']);
    });

    $this->actingAs($this->admin)
        ->post(route('users.concat.link', $user), ['concat_user_id' => '4821'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->concat_user_id)->toBe('4821');
});

it('shows an error when the given ConCat user ID does not exist', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getUserById')->once()->andReturn(null);
    });

    $this->actingAs($this->admin)
        ->post(route('users.concat.link', $user), ['concat_user_id' => 'bogus'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($user->fresh()->concat_user_id)->toBeNull();
});

it('unlinks a user and revokes every grant it held', function () {
    $sector = Sector::factory()->create();
    $user = User::factory()->create(['onboarded_at' => now(), 'concat_user_id' => 'concat-user-1', 'concat_checked_at' => now()]);
    ConcatUserRoleGrant::create([
        'user_id' => $user->id,
        'sector_id' => $sector->id,
        'concat_user_id' => 'concat-user-1',
        'concat_role_id' => 'role-123',
        'granted_at' => now(),
    ]);

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('revokeRole')->once()->with('concat-user-1', 'role-123')->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->delete(route('users.concat.unlink', $user))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->concat_user_id)->toBeNull();
    expect(ConcatUserRoleGrant::where('user_id', $user->id)->exists())->toBeFalse();
});

it('forbids users without manage-users permission from linking accounts', function () {
    $viewer = User::factory()->create(['onboarded_at' => now()]);
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($viewer)
        ->post(route('users.concat.link', $user), ['concat_user_id' => '4821'])
        ->assertForbidden();
});

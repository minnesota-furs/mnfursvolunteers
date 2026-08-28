<?php

use App\Models\ApplicationSetting;
use App\Models\ConcatSectorRoleMapping;
use App\Models\ConcatUserRoleGrant;
use App\Models\Sector;
use App\Models\User;
use App\Services\ConcatService;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['onboarded_at' => now()]);
});

it('renders the integrations tab when ConCat is not connected', function () {
    $this->actingAs($this->admin)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('Integrate with Concat')
        ->assertSee('Setting up the ConCat app')
        ->assertSee('user:read')
        ->assertSee('user:roles:update')
        ->assertSee('Callback / redirect URI')
        ->assertDontSee('Manage Concat');
});

it('renders the integrations tab with a status card when ConCat is connected', function () {
    ApplicationSetting::set('concat_api_base_url', 'https://fm-test.concat.app', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_secret', 'super-secret', 'encrypted', null, 'integrations');

    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('testConnection')->once()->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('Manage Concat')
        ->assertSee('Connected to')
        ->assertDontSee('Integrate with Concat');
});

it('connects ConCat when the credentials are valid', function () {
    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('testConnection')->once()->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->from(route('settings.index'))
        ->put(route('settings.update'), [
            'concat_api_base_url' => 'https://fm-test.concat.app',
            'concat_client_id' => 'client-id-123',
            'concat_client_secret' => 'super-secret',
        ])
        ->assertRedirect(route('settings.index'))
        ->assertSessionHas('success');

    expect(ApplicationSetting::get('concat_client_id'))->toBe('client-id-123');
    expect(ApplicationSetting::get('concat_client_secret'))->toBe('super-secret');
});

it('does not save ConCat credentials that fail to connect', function () {
    $this->mock(ConcatService::class, function ($mock) {
        $mock->shouldReceive('testConnection')->once()->andReturn(false);
    });

    $this->actingAs($this->admin)
        ->from(route('settings.index'))
        ->put(route('settings.update'), [
            'concat_api_base_url' => 'https://fm-test.concat.app',
            'concat_client_id' => 'bad-client-id',
            'concat_client_secret' => 'bad-secret',
        ])
        ->assertRedirect(route('settings.index'))
        ->assertSessionHas('error');

    expect(ApplicationSetting::get('concat_client_id'))->toBeNull();
});

it('revokes every grant and clears local state when disconnecting ConCat', function () {
    ApplicationSetting::set('concat_api_base_url', 'https://fm-test.concat.app', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_secret', 'super-secret', 'encrypted', null, 'integrations');

    $sector = Sector::factory()->create();
    $mapping = ConcatSectorRoleMapping::create([
        'sector_id' => $sector->id,
        'concat_role_id' => 'role-123',
        'concat_role_name' => 'Staff',
        'concat_scope' => 'convention',
    ]);
    $user = User::factory()->create();
    $grant = ConcatUserRoleGrant::create([
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
        ->delete(route('settings.concat-disconnect'))
        ->assertRedirect(route('settings.index'))
        ->assertSessionHas('success');

    expect(ApplicationSetting::get('concat_client_id'))->toBeNull();
    expect(ConcatUserRoleGrant::count())->toBe(0);
    expect(ConcatSectorRoleMapping::count())->toBe(0);
});

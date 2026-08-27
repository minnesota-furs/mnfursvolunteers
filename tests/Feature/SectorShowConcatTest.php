<?php

use App\Models\ApplicationSetting;
use App\Models\ConcatSectorRoleMapping;
use App\Models\Sector;
use App\Models\User;

it('hides the ConCat row when ConCat is not connected', function () {
    $sector = Sector::factory()->create();
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('sectors.show', $sector))
        ->assertOk()
        ->assertDontSee('ConCat');
});

describe('when ConCat is connected', function () {
    beforeEach(function () {
        ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
    });

    it('shows that the sector grants a role when it is watched', function () {
        $sector = Sector::factory()->create();
        ConcatSectorRoleMapping::create([
            'sector_id' => $sector->id,
            'concat_role_id' => 'role-123',
            'concat_role_name' => 'Staff',
            'concat_scope' => 'convention',
        ]);
        $user = User::factory()->create(['onboarded_at' => now()]);

        $this->actingAs($user)
            ->get(route('sectors.show', $sector))
            ->assertOk()
            ->assertSee('ConCat')
            ->assertSee('Grants the')
            ->assertSee('Staff')
            ->assertSee('this convention');
    });

    it('shows a global scope label when the mapping is global', function () {
        $sector = Sector::factory()->create();
        ConcatSectorRoleMapping::create([
            'sector_id' => $sector->id,
            'concat_role_id' => 'role-123',
            'concat_role_name' => 'Staff',
            'concat_scope' => 'global',
        ]);
        $user = User::factory()->create(['onboarded_at' => now()]);

        $this->actingAs($user)
            ->get(route('sectors.show', $sector))
            ->assertOk()
            ->assertSee('global');
    });

    it('shows that the sector does not grant a role when it is unwatched', function () {
        $sector = Sector::factory()->create();
        $user = User::factory()->create(['onboarded_at' => now()]);

        $this->actingAs($user)
            ->get(route('sectors.show', $sector))
            ->assertOk()
            ->assertSee('Does not grant a ConCat role');
    });

    it('shows a Manage Concat link to admins but not to regular users', function () {
        $sector = Sector::factory()->create();
        $admin = User::factory()->admin()->create(['onboarded_at' => now()]);
        $volunteer = User::factory()->create(['onboarded_at' => now()]);

        $this->actingAs($admin)
            ->get(route('sectors.show', $sector))
            ->assertOk()
            ->assertSee(route('admin.concat.index'), false);

        $this->actingAs($volunteer)
            ->get(route('sectors.show', $sector))
            ->assertOk()
            ->assertDontSee(route('admin.concat.index'), false);
    });
});

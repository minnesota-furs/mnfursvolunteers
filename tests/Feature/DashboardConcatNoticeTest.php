<?php

use App\Models\ApplicationSetting;
use App\Models\ConcatSectorRoleMapping;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;

function makeWatchedSector(): Sector
{
    $sector = Sector::factory()->create();
    ConcatSectorRoleMapping::create([
        'sector_id' => $sector->id,
        'concat_role_id' => 'role-123',
        'concat_role_name' => 'Staff',
        'concat_scope' => 'convention',
    ]);

    return $sector;
}

it('does not show the notice when ConCat is not connected', function () {
    $sector = makeWatchedSector();
    $user = User::factory()->create(['onboarded_at' => now()]);
    Department::factory()->create(['sector_id' => $sector->id])->users()->attach($user);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Connect Your ConCat Account');
});

describe('when ConCat is connected', function () {
    beforeEach(function () {
        ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
    });

    it('does not show the notice for a user with no department in a watched sector', function () {
        makeWatchedSector();
        $unwatchedSector = Sector::factory()->create();
        $user = User::factory()->create(['onboarded_at' => now()]);
        Department::factory()->create(['sector_id' => $unwatchedSector->id])->users()->attach($user);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Connect Your ConCat Account');
    });

    it('does not show the notice for a user with no departments at all', function () {
        makeWatchedSector();
        $user = User::factory()->create(['onboarded_at' => now()]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Connect Your ConCat Account');
    });

    it('does not show the notice when the user is already linked', function () {
        $sector = makeWatchedSector();
        $user = User::factory()->create(['onboarded_at' => now(), 'concat_user_id' => 'concat-1']);
        Department::factory()->create(['sector_id' => $sector->id])->users()->attach($user);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Connect Your ConCat Account');
    });

    it('shows the notice with a link to the profile page for an unlinked user in a watched sector', function () {
        $sector = makeWatchedSector();
        $user = User::factory()->create(['onboarded_at' => now()]);
        Department::factory()->create(['sector_id' => $sector->id])->users()->attach($user);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Connect Your ConCat Account')
            ->assertSee(route('profile.edit'), false);
    });
});

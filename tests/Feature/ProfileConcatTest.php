<?php

use App\Models\ApplicationSetting;
use App\Models\ConcatSectorRoleMapping;
use App\Models\ConcatUserRoleGrant;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use App\Services\ConcatService;

it('hides the Concat section when ConCat is not connected', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('id="concat"', false)
        ->assertDontSee('href="#concat"', false);
});

describe('when ConCat is connected', function () {
    beforeEach(function () {
        ApplicationSetting::set('concat_api_base_url', 'https://fm-test.concat.app', 'string', null, 'integrations');
        ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
        ApplicationSetting::set('concat_client_secret', 'super-secret', 'encrypted', null, 'integrations');
    });

    it('shows the Concat section right after Timezone', function () {
        $user = User::factory()->create(['onboarded_at' => now()]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeInOrder(['href="#timezone"', 'href="#concat"', 'href="#email-preferences"'], false)
            ->assertSeeInOrder(['id="timezone"', 'id="concat"', 'id="email-preferences"'], false)
            ->assertSee('Link My ConCat Account');
    });

    it('tells an unlinked user where to create a ConCat account and warns against duplicates', function () {
        $user = User::factory()->create(['onboarded_at' => now()]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('https://fm-test.concat.app', false)
            ->assertSee('Before creating a new account')
            ->assertSee("don't already have", false)
            ->assertSee('Linking the wrong account');
    });

    it('tells a user with no qualifying departments to contact Staff Admin', function () {
        $sector = Sector::factory()->create();
        ConcatSectorRoleMapping::create([
            'sector_id' => $sector->id,
            'concat_role_id' => 'role-123',
            'concat_role_name' => 'Staff',
            'concat_scope' => 'convention',
        ]);
        $otherSector = Sector::factory()->create();
        $user = User::factory()->create(['onboarded_at' => now()]);
        Department::factory()->create(['sector_id' => $otherSector->id])->users()->attach($user);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee("You don't currently have any departments that qualify you for a ConCat staff role.", false)
            ->assertSee('Staff Admin');
    });

    it('does not show the no-qualifying-departments note for a user in a watched sector', function () {
        $sector = Sector::factory()->create();
        ConcatSectorRoleMapping::create([
            'sector_id' => $sector->id,
            'concat_role_id' => 'role-123',
            'concat_role_name' => 'Staff',
            'concat_scope' => 'convention',
        ]);
        $user = User::factory()->create(['onboarded_at' => now()]);
        Department::factory()->create(['sector_id' => $sector->id])->users()->attach($user);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee("You don't currently have any departments that qualify you for a ConCat staff role.", false);
    });

    it('does not show account-creation guidance once the user is linked', function () {
        $user = User::factory()->create(['onboarded_at' => now(), 'concat_user_id' => 'concat-1']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Before creating a new account');
    });

    it('self-links using the volunteer\'s own email by default', function () {
        $user = User::factory()->create(['onboarded_at' => now(), 'email' => 'volunteer@example.com']);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->with('volunteer@example.com')
                ->andReturn(['id' => 'concat-1', 'firstName' => 'Jane', 'lastName' => 'Doe', 'email' => 'volunteer@example.com']);
        });

        $this->actingAs($user)
            ->post(route('profile.link-concat'))
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('success');

        expect($user->fresh()->concat_user_id)->toBe('concat-1');
    });

    it('self-links using a different email when the legal name matches', function () {
        $user = User::factory()->create([
            'onboarded_at' => now(),
            'email' => 'volunteer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->with('other@example.com')
                ->andReturn(['id' => 'concat-2', 'firstName' => 'jane', 'lastName' => ' Doe ', 'email' => 'other@example.com']);
        });

        $this->actingAs($user)
            ->post(route('profile.link-concat'), ['concat_search_email' => 'other@example.com'])
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('success');

        expect($user->fresh()->concat_user_id)->toBe('concat-2');
    });

    it('rejects a different-email link when the legal name does not match', function () {
        $user = User::factory()->create([
            'onboarded_at' => now(),
            'email' => 'volunteer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->with('other@example.com')
                ->andReturn(['id' => 'concat-2', 'firstName' => 'John', 'lastName' => 'Smith', 'email' => 'other@example.com']);
        });

        $this->actingAs($user)
            ->post(route('profile.link-concat'), ['concat_search_email' => 'other@example.com'])
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('error', 'Problem linking accounts. Error: 1004');

        expect($user->fresh()->concat_user_id)->toBeNull();
    });

    it('rejects a different-email link when the volunteer has no legal name on file', function () {
        $user = User::factory()->create([
            'onboarded_at' => now(),
            'email' => 'volunteer@example.com',
            'first_name' => null,
            'last_name' => null,
        ]);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->with('other@example.com')
                ->andReturn(['id' => 'concat-2', 'firstName' => 'Jane', 'lastName' => 'Doe', 'email' => 'other@example.com']);
        });

        $this->actingAs($user)
            ->post(route('profile.link-concat'), ['concat_search_email' => 'other@example.com'])
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('error', 'Problem linking accounts. Error: 1003');

        expect($user->fresh()->concat_user_id)->toBeNull();
    });

    it('does not require a legal-name match when linking with the volunteer\'s own email', function () {
        $user = User::factory()->create([
            'onboarded_at' => now(),
            'email' => 'volunteer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->with('volunteer@example.com')
                ->andReturn(['id' => 'concat-1', 'firstName' => 'Completely', 'lastName' => 'Different', 'email' => 'volunteer@example.com']);
        });

        $this->actingAs($user)
            ->post(route('profile.link-concat'))
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('success');

        expect($user->fresh()->concat_user_id)->toBe('concat-1');
    });

    it('shows a vague error with a lookup code when no ConCat account matches', function () {
        $user = User::factory()->create(['onboarded_at' => now(), 'email' => 'volunteer@example.com']);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->andReturn(null);
        });

        $this->actingAs($user)
            ->post(route('profile.link-concat'))
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('error', 'Problem linking accounts. Error: 1002');

        expect($user->fresh()->concat_user_id)->toBeNull();
    });

    it('shows a vague error with a lookup code when ConCat is not connected', function () {
        $user = User::factory()->create(['onboarded_at' => now(), 'email' => 'volunteer@example.com']);

        $this->mock(ConcatService::class, fn ($mock) => $mock->shouldReceive('isConfigured')->andReturn(false));

        $this->actingAs($user)
            ->post(route('profile.link-concat'))
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('error', 'Problem linking accounts. Error: 1001');
    });

    it('never leaks the underlying reason (e.g. name mismatch) in the displayed message', function () {
        $user = User::factory()->create([
            'onboarded_at' => now(),
            'email' => 'volunteer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->with('other@example.com')
                ->andReturn(['id' => 'concat-2', 'firstName' => 'John', 'lastName' => 'Smith', 'email' => 'other@example.com']);
        });

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('profile.link-concat'), ['concat_search_email' => 'other@example.com'])
            ->assertOk()
            ->assertSee('Problem linking accounts. Error: 1004')
            ->assertDontSee('John Smith');
    });

    it('actually displays the error message on the page after a failed link attempt', function () {
        $user = User::factory()->create(['onboarded_at' => now(), 'email' => 'volunteer@example.com']);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->andReturn(null);
        });

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('profile.link-concat'))
            ->assertOk()
            ->assertSee('text-red-600', false)
            ->assertSee('Problem linking accounts. Error: 1002');
    });

    it('shows the concat error via the toast and the contextual red text, but not the generic page banner too', function () {
        $user = User::factory()->create(['onboarded_at' => now(), 'email' => 'volunteer@example.com']);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->andReturn(null);
        });

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('profile.link-concat'))
            ->assertOk();

        // One from the toast (kept on every page) and one from the contextual red
        // text next to the button — the generic page banner should sit this one out.
        $occurrences = substr_count($response->getContent(), 'Problem linking accounts. Error: 1002');
        expect($occurrences)->toBe(2);
    });

    it('reopens the alternate-email form with the entered value after a failed search', function () {
        $user = User::factory()->create(['onboarded_at' => now(), 'email' => 'volunteer@example.com']);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('findUserByEmail')->once()->with('other@example.com')->andReturn(null);
        });

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('profile.link-concat'), ['concat_search_email' => 'other@example.com'])
            ->assertOk()
            ->assertSee('showEmailOverride: true', false)
            ->assertSee('value="other@example.com"', false);
    });

    it('lets a volunteer unlink their own ConCat account', function () {
        $sector = Sector::factory()->create();
        ConcatSectorRoleMapping::create([
            'sector_id' => $sector->id,
            'concat_role_id' => 'role-123',
            'concat_role_name' => 'Staff',
            'concat_scope' => 'convention',
        ]);
        $user = User::factory()->create(['onboarded_at' => now(), 'concat_user_id' => 'concat-1', 'concat_checked_at' => now()]);
        ConcatUserRoleGrant::create([
            'user_id' => $user->id,
            'sector_id' => $sector->id,
            'concat_user_id' => 'concat-1',
            'concat_role_id' => 'role-123',
            'granted_at' => now(),
        ]);

        $this->mock(ConcatService::class, function ($mock) {
            $mock->shouldReceive('revokeRole')->once()->with('concat-1', 'role-123')->andReturn(true);
        });

        $this->actingAs($user)
            ->delete(route('profile.unlink-concat'))
            ->assertRedirect(route('profile.edit').'#concat')
            ->assertSessionHas('success');

        expect($user->fresh()->concat_user_id)->toBeNull();
        expect(ConcatUserRoleGrant::where('user_id', $user->id)->exists())->toBeFalse();
    });
});

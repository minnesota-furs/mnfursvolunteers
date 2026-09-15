<?php

use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;

test('profile page is displayed', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response
        ->assertOk()
        ->assertSee('aria-label="Profile sections"', false)
        ->assertSeeInOrder([
            '<aside',
            '</aside>',
            '<main',
            '</main>',
        ], false)
        ->assertSeeInOrder([
            'href="#profile-information"',
            'href="#departments"',
            'href="#accessibility-needs"',
            'href="#timezone"',
            'href="#email-preferences"',
            'href="#calendar"',
            'href="#password"',
            'href="#security"',
            'href="#delete-account"',
        ], false)
        ->assertSeeInOrder([
            'id="profile-information"',
            'id="departments"',
            'id="accessibility-needs"',
            'id="timezone"',
            'id="email-preferences"',
            'id="calendar"',
            'id="password"',
            'id="security"',
            'id="delete-account"',
        ], false)
        ->assertSee('href="'.route('onboarding.index', ['step' => 1]).'"', false)
        ->assertSee('Run through the onboarding wizard again.');
});

test('profile page lists department assignments with links and sectors', function () {
    $firstSector = Sector::factory()->create(['name' => 'Convention Operations']);
    $secondSector = Sector::factory()->create(['name' => 'Community']);
    $firstDepartment = Department::factory()->for($firstSector)->create(['name' => 'Registration']);
    $secondDepartment = Department::factory()->for($secondSector)->create(['name' => 'Outreach']);
    $user = User::factory()->create(['onboarded_at' => now()]);
    $user->departments()->attach([$firstDepartment->id, $secondDepartment->id]);
    $user->headDepartments()->attach($firstDepartment);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSeeTextInOrder(['Your Departments', 'Registration', 'Convention Operations', 'Outreach', 'Community'])
        ->assertSee('href="'.route('departments.show', $firstDepartment).'"', false)
        ->assertSee('href="'.route('departments.show', $secondDepartment).'"', false)
        ->assertSee('data-department-head="true"', false)
        ->assertSee('border-amber-300 bg-amber-50', false)
        ->assertSeeText('Department Head')
        ->assertSeeText('If your departments are missing or incorrect, please reach out to a staff administrator.')
        ->assertDontSeeText('You have no staffing commitments to any departments');
});

test('profile page shows an empty state when the user has no departments', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSeeText('You have no staffing commitments to any departments')
        ->assertSeeText('If your departments are missing or incorrect, please reach out to a staff administrator.');
});

test('profile information can be updated', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('security section shows an empty state when no oauth apps are authorized', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSeeText('Authorized Applications')
        ->assertSeeText("You haven't authorized any applications.");
});

test('security section lists authorized oauth applications', function (): void {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $client = app(ClientRepository::class)->create(null, 'Test App', 'https://example.com/callback');

    Token::create([
        'id' => Str::random(80),
        'user_id' => $user->id,
        'client_id' => $client->id,
        'name' => null,
        'scopes' => ['identity'],
        'revoked' => false,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSeeText('Test App')
        ->assertDontSeeText('identity')
        ->assertSee(route('profile.revoke-oauth-client', $client), false);
});

test('security section does not list other users authorizations or revoked tokens', function (): void {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $otherUser = User::factory()->create(['onboarded_at' => now()]);
    $client = app(ClientRepository::class)->create(null, 'Test App', 'https://example.com/callback');
    $revokedClient = app(ClientRepository::class)->create(null, 'Revoked App', 'https://example.com/callback');

    Token::create([
        'id' => Str::random(80),
        'user_id' => $otherUser->id,
        'client_id' => $client->id,
        'scopes' => ['identity'],
        'revoked' => false,
    ]);

    Token::create([
        'id' => Str::random(80),
        'user_id' => $user->id,
        'client_id' => $revokedClient->id,
        'scopes' => ['identity'],
        'revoked' => true,
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSeeText('Test App')
        ->assertDontSeeText('Revoked App')
        ->assertSeeText("You haven't authorized any applications.");
});

test('user can revoke their own authorization for an oauth application', function (): void {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $client = app(ClientRepository::class)->create(null, 'Test App', 'https://example.com/callback');

    Token::create([
        'id' => Str::random(80),
        'user_id' => $user->id,
        'client_id' => $client->id,
        'scopes' => ['identity'],
        'revoked' => false,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('profile.revoke-oauth-client', $client));

    $response->assertRedirect(route('profile.edit').'#security');

    $this->assertDatabaseHas('oauth_access_tokens', [
        'user_id' => $user->id,
        'client_id' => $client->id,
        'revoked' => true,
    ]);
});

test('revoking an oauth application only affects the current user', function (): void {
    $user = User::factory()->create(['onboarded_at' => now()]);
    $otherUser = User::factory()->create(['onboarded_at' => now()]);
    $client = app(ClientRepository::class)->create(null, 'Test App', 'https://example.com/callback');

    Token::create([
        'id' => Str::random(80),
        'user_id' => $otherUser->id,
        'client_id' => $client->id,
        'scopes' => ['identity'],
        'revoked' => false,
    ]);

    $this->actingAs($user)->delete(route('profile.revoke-oauth-client', $client));

    $this->assertDatabaseHas('oauth_access_tokens', [
        'user_id' => $otherUser->id,
        'client_id' => $client->id,
        'revoked' => false,
    ]);
});

<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

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
            'href="#accessibility-needs"',
            'href="#timezone"',
            'href="#email-preferences"',
            'href="#calendar"',
            'href="#password"',
            'href="#delete-account"',
        ], false)
        ->assertSeeInOrder([
            'id="profile-information"',
            'id="accessibility-needs"',
            'id="timezone"',
            'id="email-preferences"',
            'id="calendar"',
            'id="password"',
            'id="delete-account"',
        ], false)
        ->assertSee('href="'.route('onboarding.index', ['step' => 1]).'"', false)
        ->assertSee('Run through the onboarding wizard again.');
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

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
    $user = User::factory()->create();

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
    $user = User::factory()->create();

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
    $user = User::factory()->create();

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

<?php

use App\Models\ApplicationSetting;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('onboarding.index'));
});

test('users must acknowledge a configured warning email domain', function () {
    ApplicationSetting::set('warning_email_domains', "MNFURS.ORG\nfurrymigration.org");

    $registration = [
        'name' => 'Test User',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'volunteer@mnfurs.org',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $this->post('/register', $registration)
        ->assertSessionHasErrors('warning_email_acknowledged');

    $this->assertGuest();

    $this->post('/register', $registration + ['warning_email_acknowledged' => '1'])
        ->assertRedirect(route('onboarding.index'));

    $this->assertAuthenticated();
});

test('warning email domains are disabled when the setting is blank', function () {
    ApplicationSetting::set('warning_email_domains', '');

    $this->post('/register', [
        'name' => 'Test User',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'volunteer@mnfurs.org',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('onboarding.index'));

    $this->assertAuthenticated();
});

test('a configured warning list does not affect personal email domains', function () {
    ApplicationSetting::set('warning_email_domains', 'mnfurs.org, furrymigration.org');

    $this->post('/register', [
        'name' => 'Test User',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'volunteer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('onboarding.index'));

    $this->assertAuthenticated();
});

test('an admin can configure multiple warning email domains', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('settings.update'), [
            'warning_email_domains' => "mnfurs.org\nfurrymigration.org",
        ])
        ->assertRedirect(route('settings.index'));

    expect(ApplicationSetting::get('warning_email_domains'))
        ->toBe("mnfurs.org\nfurrymigration.org");
});

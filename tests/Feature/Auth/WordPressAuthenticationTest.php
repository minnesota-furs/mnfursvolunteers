<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('shows a warning instead of creating an account after a valid new WordPress login', function () {
    config()->set('database.connections.wordpress', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => 'wp_',
        'foreign_key_constraints' => true,
    ]);

    Schema::connection('wordpress')->create('users', function (Blueprint $table) {
        $table->unsignedBigInteger('ID')->primary();
        $table->string('user_login');
        $table->string('user_email');
        $table->string('user_pass');
    });

    $password = 'valid-password';
    $passwordToHash = base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true));

    DB::connection('wordpress')->table('users')->insert([
        'ID' => 123,
        'user_login' => 'new-volunteer',
        'user_email' => 'new-volunteer@example.com',
        'user_pass' => '$wp'.password_hash($passwordToHash, PASSWORD_DEFAULT),
    ]);

    $response = $this->post(route('wordpress.login'), [
        'email' => 'new-volunteer',
        'password' => $password,
    ]);

    $response
        ->assertOk()
        ->assertViewIs('auth.wordpress-account-warning')
        ->assertSee('Create a new volunteer account?')
        ->assertSee('sign in with its email address instead')
        ->assertSee('Go back to login')
        ->assertSee('Create New Account');

    expect(User::query()->count())->toBe(0);
});

it('creates and authenticates a confirmed WordPress account', function () {
    $response = $this->withSession([
        'wordpress.pending_user' => [
            'id' => 123,
            'name' => 'new-volunteer',
            'email' => 'new-volunteer@example.com',
        ],
    ])->post(route('wordpress.create-account'));

    $user = User::query()->where('wordpress_user_id', 123)->firstOrFail();

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));

    expect($user)
        ->name->toBe('new-volunteer')
        ->email->toBe('new-volunteer@example.com')
        ->is_linked_to_wp->toBeTruthy();
});

it('does not create an account without a pending confirmed WordPress login', function () {
    $response = $this->post(route('wordpress.create-account'));

    $this->assertGuest();
    $response
        ->assertRedirect(route('wordpress.login'))
        ->assertSessionHasErrors('email');

    expect(User::query()->count())->toBe(0);
});

it('clears the pending WordPress account when returning to login', function () {
    $response = $this->withSession([
        'wordpress.pending_user' => [
            'id' => 123,
            'name' => 'new-volunteer',
            'email' => 'new-volunteer@example.com',
        ],
    ])->post(route('wordpress.cancel-account'));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionMissing('wordpress.pending_user');

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});

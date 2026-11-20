<?php

use App\Models\User;

beforeEach(function () {
    $this->enableSetupRedirect();
});

it('redirects to the setup wizard when no users exist', function () {
    $this->get(route('login'))->assertRedirect(route('setup.index'));
});

it('shows the setup wizard when no users exist', function () {
    $this->get(route('setup.index'))->assertOk();
});

it('lets requests through once a user exists', function () {
    User::factory()->create();

    $this->get(route('login'))->assertOk();
});

it('redirects away from the setup wizard once a user exists', function () {
    User::factory()->create();

    $this->get(route('setup.index'))->assertRedirect(route('dashboard'));
});

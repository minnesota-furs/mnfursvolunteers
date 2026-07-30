<?php

use App\Models\User;
use Illuminate\Support\Collection;

it('explains relationship markers and their privacy protections', function () {
    $this->actingAs(User::factory()->create());

    $view = $this->view('relationships.index', [
        'favorites' => new Collection,
        'avoided' => new Collection,
    ]);

    $view
        ->assertSee('What is this?')
        ->assertSee('How Favorite & Avoid works', false)
        ->assertSee('relationship-help')
        ->assertSee('A person will never be notified that you marked them as avoid')
        ->assertSee('Leadership can see when someone has received a high number of avoid markers')
        ->assertSee('This review is always handled anonymously.');
});

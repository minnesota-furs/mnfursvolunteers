<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Auth\Events\Registered;

class NotifyAdminsOfNewUser
{
    public function handle(Registered $event): void
    {
        $newUser = $event->user;

        User::where('admin', true)->each(function (User $admin) use ($newUser) {
            $admin->notify(new AppNotification(
                title: 'New user registered',
                message: "{$newUser->name} ({$newUser->email}) has just created an account.",
                url: route('users.show', $newUser->id),
            ));
        });
    }
}

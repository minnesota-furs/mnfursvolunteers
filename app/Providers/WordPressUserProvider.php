<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Corcel\Model\User as WPUser;
use App\Helpers\WordPressHasher;


class WordPressUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        return WPUser::where('user_login', $credentials['email'])->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return WordPressHasher::check($credentials['password'], $user->user_pass);
    }
}

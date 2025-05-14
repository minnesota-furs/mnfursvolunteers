<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Providers\WordPressUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Auth::provider('wordpress', function ($app, array $config) {
            return new WordPressUserProvider($app['hash'], $config['model']);
        });

        // Define Gates
        foreach (config('permissions') as $key => $permission) {
            Gate::define($key, fn ($user) => $user->hasPermission($permission['label']));
        }
    }
}

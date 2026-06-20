<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Providers\WordPressUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use Laravel\Passport\Passport;

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
     * Register any application services.
     *
     * Passport's own service provider registers its default routes during
     * its boot() call, which runs before this provider's boot(). Disabling
     * them has to happen in register() to take effect in time.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();
    }

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

        // Passport registers a much larger set of routes by default
        // (client/personal-access-token self-service JSON APIs) that our
        // User model doesn't support. We only need the authorization-code
        // (PKCE) flow, so register just those routes ourselves. Clients are
        // managed by admins at /settings/oauth-setup instead.
        $this->registerPassportRoutes();

        Passport::tokensExpireIn(now()->addDay());
        Passport::refreshTokensExpireIn(now()->addDays(30));

        Passport::tokensCan([
            'identity' => 'View your basic identity (name and email)',
            'volunteer-info' => 'View your volunteer info (department, sector, and admin/staff status)',
        ]);
        Passport::setDefaultScope(['identity']);
    }

    /**
     * Register only the OAuth authorization-code (PKCE) and token endpoints.
     */
    protected function registerPassportRoutes(): void
    {
        Route::group([
            'as' => 'passport.',
            'prefix' => config('passport.path', 'oauth'),
            'namespace' => 'Laravel\Passport\Http\Controllers',
        ], function () {
            Route::post('/token', [
                'uses' => 'AccessTokenController@issueToken',
                'as' => 'token',
                'middleware' => 'throttle',
            ]);

            Route::get('/authorize', [
                'uses' => 'AuthorizationController@authorize',
                'as' => 'authorizations.authorize',
                'middleware' => 'web',
            ]);

            Route::middleware(['web', 'auth'])->group(function () {
                Route::post('/authorize', [
                    'uses' => 'ApproveAuthorizationController@approve',
                    'as' => 'authorizations.approve',
                ]);

                Route::delete('/authorize', [
                    'uses' => 'DenyAuthorizationController@deny',
                    'as' => 'authorizations.deny',
                ]);
            });
        });
    }
}

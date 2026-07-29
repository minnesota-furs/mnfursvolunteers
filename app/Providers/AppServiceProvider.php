<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Models\ApplicationSetting;
use App\Models\User;
use App\Models\Event;
use App\Models\Shift;
use App\Observers\AuditableObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(AuditableObserver::class);
        Event::observe(AuditableObserver::class);
        Shift::observe(AuditableObserver::class);

        $this->applyTimezoneOverride();
        $this->registerFeatureBladeDirectives();
        $this->registerViewComposers();
    }

    /**
     * Apply the admin-configured timezone (Settings > Contact tab) in place of
     * config/app.php's `timezone`, if one has been set. Runs in boot() rather than
     * register() so the database connection is available. config('app.timezone_default')
     * is kept as the unmodified config/app.php value so the settings UI can display it
     * even after the override below has replaced 'app.timezone'.
     */
    protected function applyTimezoneOverride(): void
    {
        $configured = config('app.timezone', 'UTC');
        config(['app.timezone_default' => $configured]);

        try {
            $override = ApplicationSetting::get('app_timezone');
        } catch (\Throwable) {
            // Settings table may not exist yet (e.g. before initial migration).
            return;
        }

        if ($override && $override !== $configured) {
            config(['app.timezone' => $override]);
            date_default_timezone_set($override);
        }
    }

    /**
     * Share notification data with the navigation view.
     */
    protected function registerViewComposers(): void
    {
        View::composer('layouts.navigation', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $unreadNotificationsCount = $user->unreadNotifications()->count();
                $recentNotifications = $user->unreadNotifications()->latest()->take(5)->get();
            } else {
                $unreadNotificationsCount = 0;
                $recentNotifications = collect();
            }

            $view->with(compact('unreadNotificationsCount', 'recentNotifications'));
        });
    }

    /**
     * Register custom Blade directives for feature flags.
     */
    protected function registerFeatureBladeDirectives(): void
    {
        // @feature('feature-name') - Show content if feature is enabled
        Blade::if('feature', function (string $feature) {
            return feature_enabled($feature);
        });

        // @featureAny(['feature1', 'feature2']) - Show if any feature is enabled
        Blade::if('featureAny', function (array $features) {
            foreach ($features as $feature) {
                if (feature_enabled($feature)) {
                    return true;
                }
            }
            return false;
        });

        // @featureAll(['feature1', 'feature2']) - Show only if all features are enabled
        Blade::if('featureAll', function (array $features) {
            foreach ($features as $feature) {
                if (!feature_enabled($feature)) {
                    return false;
                }
            }
            return true;
        });

        // @featureDisabled('feature-name') - Show content if feature is disabled
        Blade::if('featureDisabled', function (string $feature) {
            return !feature_enabled($feature);
        });
    }
}

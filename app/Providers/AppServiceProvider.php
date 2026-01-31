<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
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

        $this->registerFeatureBladeDirectives();
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

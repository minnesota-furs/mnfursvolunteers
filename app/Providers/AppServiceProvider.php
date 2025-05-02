<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\Settings\GeneralSettings;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Share the 'page_help' setting with all views
        View::composer('*', function ($view) {
            $settings = app(GeneralSettings::class);
            $view->with('site_name', $settings->site_name);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

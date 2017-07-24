<?php

namespace Point\BumiShares;

use Illuminate\Support\ServiceProvider;

class BumiSharesServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // register routes file
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/Http/routes.php';
        }

        // publish views
        $this->loadViewsFrom(__DIR__ . '/views', 'bumi-shares');

        $this->publishes([
            __DIR__ . '/views/app/menu/facility' => base_path('resources/views/menu/facility'),
        ], 'menus');

        // publish migration
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/database/seeds' => database_path('seeds'),
        ], 'seeds');
        
        // all in one publish
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
            __DIR__ . '/database/seeds' => database_path('seeds'),
            __DIR__ . '/views/app/menu/facility' => base_path('resources/views/menu/facility'),
        ], 'setup');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
    }
}

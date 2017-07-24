<?php

namespace Point\BumiDeposit;

use Illuminate\Support\ServiceProvider;

class BumiDepositServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/views', 'bumi-deposit');

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

        // publish lang
        $this->loadTranslationsFrom(__DIR__.'/lang', 'bumi-deposit');

        $this->publishes([
            __DIR__ . '/lang' => base_path('resources/lang'),
        ], 'lang');
        
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

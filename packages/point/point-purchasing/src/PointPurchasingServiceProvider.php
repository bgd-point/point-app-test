<?php

namespace Point\PointPurchasing;

use Illuminate\Support\ServiceProvider;

class PointPurchasingServiceProvider extends ServiceProvider
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
            require __DIR__ . '/Http/Routes/purchasing-service.php';
            require __DIR__ . '/Http/Routes/purchasing-inventory.php';
            require __DIR__ . '/Http/Routes/purchasing-fixed-assets.php';
        }

        // publish migration
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        // publish table seeding
        $this->publishes([
            __DIR__ . '/database/seeds' => database_path('seeds'),
        ], 'seeds');

        // publish views
        $this->loadViewsFrom(__DIR__ . '/views', 'point-purchasing');
        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/vendor/point-purchasing'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/views/app/menu/purchasing' => base_path('resources/views/menu/purchasing'),
        ], 'menus');

        // publish lang
        $this->loadTranslationsFrom(__DIR__.'/lang', 'point-purchasing');
        $this->publishes([
            __DIR__ . '/lang' => base_path('resources/lang'),
        ], 'lang');

        // all in one publish
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
            __DIR__ . '/database/seeds' => database_path('seeds'),
            __DIR__ . '/views/app/menu/purchasing' => base_path('resources/views/menu/purchasing'),
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

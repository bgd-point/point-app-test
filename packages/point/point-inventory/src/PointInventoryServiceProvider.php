<?php

namespace Point\PointInventory;

use Illuminate\Support\ServiceProvider;

class PointInventoryServiceProvider extends ServiceProvider
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
            require __DIR__ . '/Http/Routes/inventory-usage.php';
            require __DIR__ . '/Http/Routes/stock-correction.php';
            require __DIR__ . '/Http/Routes/stock-opname.php';
            require __DIR__ . '/Http/Routes/transfer-item.php';
        }

        // publish views
        $this->loadViewsFrom(__DIR__ . '/views', 'point-inventory');

        $this->publishes([
            __DIR__ . '/views/app/menu/inventory' => base_path('resources/views/menu/inventory'),
        ], 'menus');

        // publish migration
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/database/seeds' => database_path('seeds'),
        ], 'seeds');

        // publish lang
        $this->loadTranslationsFrom(__DIR__.'/lang', 'point-inventory');

        $this->publishes([
            __DIR__ . '/lang' => base_path('resources/lang'),
        ], 'lang');

        // all in one publish
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
            __DIR__ . '/database/seeds' => database_path('seeds'),
            __DIR__ . '/views/app/menu/inventory' => base_path('resources/views/menu/inventory'),
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

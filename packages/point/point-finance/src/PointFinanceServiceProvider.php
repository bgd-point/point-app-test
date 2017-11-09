<?php

namespace Point\PointFinance;

use Illuminate\Support\ServiceProvider;

class PointFinanceServiceProvider extends ServiceProvider
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
            require __DIR__ . '/Http/Routes/payment-order.php';
            require __DIR__ . '/Http/Routes/payment.php';
            require __DIR__ . '/Http/Routes/debts-aging-report.php';
        }

        // publish views
        $this->loadViewsFrom(__DIR__ . '/views', 'point-finance');

        $this->publishes([
            __DIR__ . '/views/app/menu/finance' => base_path('resources/views/menu/finance'),
        ], 'menus');

        // publish migration
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/database/seeds' => database_path('seeds'),
        ], 'seeds');

        // publish lang
        $this->loadTranslationsFrom(__DIR__.'/lang', 'point-finance');

        $this->publishes([
            __DIR__ . '/lang' => base_path('resources/lang'),
        ], 'lang');
        
        // all in one publish
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
            __DIR__ . '/database/seeds' => database_path('seeds'),
            __DIR__ . '/views/app/menu/finance' => base_path('resources/views/menu/finance'),
        ], 'setup');

        $this->publishes([
            __DIR__ . '/views/app/menu/finance' => base_path('resources/views/menu/finance'),
        ], 'menu');
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

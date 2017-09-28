<?php

namespace Point\PointSales;

use Illuminate\Support\ServiceProvider;

class PointSalesServiceProvider extends ServiceProvider
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
            require __DIR__ . '/Http/Routes/sales.php';
            require __DIR__ . '/Http/Routes/sales-quotation.php';
            require __DIR__ . '/Http/Routes/sales-order.php';
            require __DIR__ . '/Http/Routes/sales-downpayment.php';
            require __DIR__ . '/Http/Routes/sales-delivery-order.php';
            require __DIR__ . '/Http/Routes/sales-invoice.php';
            require __DIR__ . '/Http/Routes/sales-report.php';
            require __DIR__ . '/Http/Routes/sales-retur.php';
            require __DIR__ . '/Http/Routes/sales-payment-collection.php';
            require __DIR__ . '/Http/Routes/sales-service.php';
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
        $this->loadViewsFrom(__DIR__ . '/views', 'point-sales');
        $this->publishes([
            __DIR__ . '/views/app/menu/sales' => base_path('resources/views/menu/sales'),
        ], 'menus');

        // publish lang
        $this->loadTranslationsFrom(__DIR__.'/lang', 'basic-sales');
        $this->publishes([
            __DIR__ . '/lang' => base_path('resources/lang'),
        ], 'lang');

        // all in one publish
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
            __DIR__ . '/database/seeds' => database_path('seeds'),
            __DIR__ . '/views/app/menu/sales' => base_path('resources/views/menu/sales'),
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

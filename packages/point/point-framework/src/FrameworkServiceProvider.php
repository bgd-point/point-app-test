<?php

namespace Point\Framework;

use Illuminate\Support\ServiceProvider;

class FrameworkServiceProvider extends ServiceProvider
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
            require __DIR__ . '/Http/Routes/master.php';
            require __DIR__ . '/Http/Routes/accounting.php';
            require __DIR__ . '/Http/Routes/inventory.php';
            require __DIR__ . '/Http/Routes/facility.php';
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
        $this->loadViewsFrom(__DIR__ . '/views', 'framework');
        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/vendor/framework'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/views/app/menu/master' => base_path('resources/views/menu/master'),
            __DIR__ . '/views/app/menu/accounting' => base_path('resources/views/menu/accounting'),
            __DIR__ . '/views/app/menu/inventory' => base_path('resources/views/menu/inventory'),
        ], 'menus');

        $this->publishes([
            __DIR__ . '/../.env' => base_path('.env'),
        ], 'env');

        $this->publishes([
            __DIR__ . '/../laravel' => base_path('/'),
        ], 'laravel');

        // publish lang
        $this->loadTranslationsFrom(__DIR__.'/lang', 'framework');

        $this->publishes([
            __DIR__ . '/../laravel' => base_path('/'),
        ], 'replace-laravel-file');

        // all in one publish
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
            __DIR__ . '/database/seeds' => database_path('seeds'),
            __DIR__ . '/views/app/menu/master' => base_path('resources/views/menu/master'),
            __DIR__ . '/views/app/menu/accounting' => base_path('resources/views/menu/accounting'),
            __DIR__ . '/views/app/menu/inventory' => base_path('resources/views/menu/inventory'),
        ], 'setup');
    }

    protected $commands = [
        'Point\Framework\Console\Commands\DefaultCoaAccount',
    ];

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // register service providers
        $this->app->register('Point\Core\CoreServiceProvider');

        // register commands
        $this->commands($this->commands);

        // include helpers file to the project
        $helpers_file = __DIR__ . '/helpers.php';
        if (file_exists($helpers_file)) {
            require_once($helpers_file);
        }

        // register aliases
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            // helpers
            $loader->alias('FormulirHelper', 'Point\Framework\Helpers\FormulirHelper');
            $loader->alias('FormulirNumberHelper', 'Point\Framework\Helpers\FormulirNumberHelper');
            $loader->alias('InventoryHelper', 'Point\Framework\Helpers\InventoryHelper');
            $loader->alias('JournalHelper', 'Point\Framework\Helpers\JournalHelper');
            $loader->alias('ReferHelper', 'Point\Framework\Helpers\ReferHelper');
        });
    }
}

<?php

namespace Point\Core;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
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
            require __DIR__ . '/Http/Routes/facility.php';
        }

        // publish migration
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations')
        ], 'migrations');

        // publish table seeding
        $this->publishes([
            __DIR__ . '/database/seeds' => database_path('seeds')
        ], 'seeds');

        // publish assets
        $this->publishes([
            __DIR__ . '/assets' => public_path('core'),
            __DIR__ . '/build' => public_path('build'),
        ], 'assets');

        // publish views
        $this->loadViewsFrom(__DIR__ . '/views', 'core');
        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/vendor/core'),
        ], 'views');

        // publish lang
        $this->loadTranslationsFrom(__DIR__.'/lang', 'core');

        // publish configs
        $this->publishes([
            __DIR__ . '/config/point.php' => config_path('point.php'),
        ], 'configs');

        // all in one publish
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
            __DIR__ . '/database/seeds' => database_path('seeds'),
            __DIR__ . '/assets' => public_path('core'),
            __DIR__ . '/build' => public_path('build'),
            __DIR__ . '/config/point.php' => config_path('point.php'),
        ], 'setup');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register('Bican\Roles\RolesServiceProvider');
        $this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
        $this->app->register('Intervention\Image\ImageServiceProvider');
        $this->app->register('Barryvdh\DomPDF\ServiceProvider');
        $this->app->register('Jenssegers\Agent\AgentServiceProvider');
        $this->app->register('Milon\Barcode\BarcodeServiceProvider');
        $this->app->register('GrahamCampbell\Flysystem\FlysystemServiceProvider');
        $this->app->register('Websight\GcsProvider\CloudStorageServiceProvider');

        // register service providers
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            // external facades
            $loader->alias('Str', 'Illuminate\Support\Str');
            $loader->alias('Carbon', 'Carbon\Carbon');
            $loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
            $loader->alias('Number', 'Point\Core\Helpers\NumberHelper');
            $loader->alias('Image', 'Intervention\Image\Facades\Image');
            $loader->alias('PDF', 'Barryvdh\DomPDF\Facade');
            $loader->alias('Agent', 'Jenssegers\Agent\Facades\Agent');
            $loader->alias('DNS1D', 'Milon\Barcode\Facades\DNS1DFacade');
            $loader->alias('DNS2D', 'Milon\Barcode\Facades\DNS2DFacade');
            $loader->alias('Flysystem', 'GrahamCampbell\Flysystem\Facades\Flysystem');
        });

        // include helpers file to the project
        $helpers_file = __DIR__ . '/helpers.php';
        if (file_exists($helpers_file)) {
            require_once($helpers_file);
        }

        // register aliases
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            // helpers
            $loader->alias('DateHelper', 'Point\Core\Helpers\DateHelper');
            $loader->alias('GritterHelper', 'Point\Core\Helpers\GritterHelper');
            $loader->alias('NumberHelper', 'Point\Core\Helpers\NumberHelper');
            $loader->alias('PermissionHelper', 'Point\Core\Helpers\PermissionHelper');
            $loader->alias('PluginHelper', 'Point\Core\Helpers\PluginHelper');
            $loader->alias('StorageHelper', 'Point\Core\Helpers\StorageHelper');
            $loader->alias('TimelineHelper', 'Point\Core\Helpers\TimelineHelper');
            $loader->alias('TempDataHelper', 'Point\Core\Helpers\TempDataHelper');
            $loader->alias('UsageLimitHelper', 'Point\Core\Helpers\UsageLimitHelper');
        });
    }
}

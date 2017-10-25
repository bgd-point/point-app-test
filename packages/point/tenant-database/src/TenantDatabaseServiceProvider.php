<?php

namespace Point\TenantDatabase;

use Illuminate\Support\ServiceProvider;

class TenantDatabaseServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('point::command.migrate.tenant.install', function ($app) {
            return new InstallTenantMigrationRepositoryCommand();
        });
        $this->app->bind('point::command.migrate.tenant', function ($app) {
            return new MigrateTenantDabataseCommand();
        });
        $this->app->bind('point::command.migrate.tenant.refresh', function ($app) {
            return new RefreshTenantDatabaseCommand();
        });
        $this->app->bind('point::command.migrate.tenant.reset', function ($app) {
            return new ResetTenantDatabaseCommand();
        });
        $this->app->bind('point::command.migrate.tenant.rollback', function ($app) {
            return new RollbackTenantDatabaseCommand();
        });
        $this->app->bind('point::command.db.tenant.seed', function ($app) {
            return new SeedTenantDatabaseCommand();
        });

        $this->commands([
            'point::command.migrate.tenant.install',
            'point::command.migrate.tenant',
            'point::command.migrate.tenant.refresh',
            'point::command.migrate.tenant.reset',
            'point::command.migrate.tenant.rollback',
            'point::command.db.tenant.seed'
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reset-database {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database and seeding data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach(DB::select('SHOW TABLES') as $table) {
            $table_array = get_object_vars($table);
            \Schema::drop($table_array[key($table_array)]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'CoreDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'FrameworkDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'CoreDefaultAdminDatabaseSeeder']);
        Artisan::call('framework:default-account', ['db_name' => $this->argument('db_name')]);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'CoreDevDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'FrameworkDevDatabaseSeeder']);

        // ADD PLUGINS SEEDING HERE
        Artisan::call('db:seed', ['--force' => true, '--class' => 'PointInventoryDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'PointPurchasingDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'PointSalesDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'PointExpeditionDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'PointManufactureDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'PointFinanceDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'PointAccountingDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'BumiDepositDatabaseSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'BumiSharesDatabaseSeeder']);

        // UPDATE ROLES ADMINISTRATOR ROLE
        Artisan::call('db:seed', ['--force' => true, '--class' => 'CoreDefaultAdminDatabaseSeeder']);
    }
}

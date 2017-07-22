<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SettingResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset-database {database_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate client database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $database_name = strtolower($this->argument('database_name'));

        // drop database
        $process = new Process('mysql -u '.env('DB_USERNAME').' -p'.env('DB_PASSWORD').' -e "drop database '.$database_name.'"');
        $process->run();
        \Log::info('SBR1');

        // create new database
        $process = new Process('mysql -u '.env('DB_USERNAME').' -p'.env('DB_PASSWORD').' -e "create database '.$database_name.'"');
        $process->run();
        \Log::info('SBR2');

        // migrate new database
        $process = new Process('php artisan migrate:tenant mysql '.$database_name);
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        // seeding new database
        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=CoreDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=FrameworkDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=BumiDepositDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=BumiSharesDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=PointPurchasingDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=PointSalesDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=PointExpeditionDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=PointManufactureDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=PointFinanceDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=PointInventoryDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=PointAccountingDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=CoreDefaultAdminDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        // DEV SEEDER
        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=CoreDevDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$database_name.'" --class=FrameworkDevDatabaseSeeder --force');
        $process->setPTY(true);
        $process->setWorkingDirectory(env('CLIENT_PATH'));
        $process->run();
        \Log::info('SBRX');

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}

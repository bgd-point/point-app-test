<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class SeedDummy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:seed-dummy {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeding dummy data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $process = new Process('php artisan db:tenant:seed mysql "'.$this->argument('db_name').'" --class=CoreDevDatabaseSeeder --force');
        $process->setPTY(true);
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$this->argument('db_name').'" --class=FrameworkDevDatabaseSeeder --force');
        $process->setPTY(true);
        $process->run();

        $process = new Process('php artisan db:tenant:seed mysql "'.$this->argument('db_name').'" --class=CoreDefaultAdminDatabaseSeeder --force');
        $process->setPTY(true);
        $process->run();
    }
}

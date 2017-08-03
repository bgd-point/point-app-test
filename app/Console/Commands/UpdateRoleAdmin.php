<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdateRoleAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:update-role-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give all permission admin role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('db:seed', ['--force' => true, '--class' => 'CoreDefaultAdminDatabaseSeeder']);
    }
}

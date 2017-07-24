<?php

namespace Point\Framework\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Point\Framework\Models\Master\Coa;

class DefaultCoaAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'framework:default-account {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set default account linked';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Start seeding default account');

        Config::set('database.connections.mysql', array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => $this->argument('db_name'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ));

        $this->info('Finish Seeding');
    }
}

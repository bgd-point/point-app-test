<?php

namespace Point\TenantDatabase;

use \Illuminate\Console\Command as Command;
use \Symfony\Component\Console\Input\InputOption as InputOption;
use \Symfony\Component\Console\Input\InputArgument as InputArgument;

class MigrateTenant extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:tenant-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
    public function fire()
    {
        $databases = ["7steps", "andika", "bananta", "bankbi", "bankbim", "bbl", "bbl2", "bbl4", "bbla", "bblarchive", "bbm", "bidepo", "big", "bim", "bintangbaru", "bintangbarutest", "bjm", "blk", "bmj", "bmp", "bmpratama", "bmr", "bmrpalu", "bns", "bumiananta", "bumifinance", "bumiindo", "bumimandiri", "bumishares", "cahayasurya", "connect", "cskj", "demo", "demo2", "demoksp", "elshada", "fiw", "gbl", "glomamu", "gmb", "gpr", "gprdwi", "gprtri", "gradin", "instruction", "kbfoodtruck", "kblidah", "kbmanukan", "kbmusi", "kbretail", "liberta", "mastergrade", "nuskb", "pastelpajamas", "personalfinance", "randusari", "skm", "test", "test05", "test3933"];

        foreach($databases as $database) {
            $connectionName = $this->argument('connection-name');
            $databaseName = $database;
            
            \Config::set('database.connections.'.$connectionName.'.database', $databaseName);
            \DB::reconnect($connectionName);
            \DB::setDefaultConnection($connectionName);

            $this->info('Creating migration table in tenant database "'.$databaseName.'"...');

            $this->call('migrate', ['--force' => true]);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('connection-name', InputArgument::REQUIRED, 'Tenant connection name.'),
            array('database-name', InputArgument::REQUIRED, 'Tenant database name.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],

            ['path', null, InputOption::VALUE_OPTIONAL, 'The path of migrations files to be executed.'],

            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],

            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
        );
    }
}

<?php

namespace Point\TenantDatabase;

use Illuminate\Console\Command as Command;
use Symfony\Component\Console\Input\InputOption as InputOption;
use Symfony\Component\Console\Input\InputArgument as InputArgument;

class SeedTenantDatabaseCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'db:tenant:seed';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '"db:seed" tenant database.';

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
		$connectionName = $this->argument('connection-name');
		$databaseName = $this->argument('database-name');

		\Config::set('database.connections.'.$connectionName.'.database', $databaseName);
		\DB::reconnect($connectionName);
		\DB::setDefaultConnection($connectionName);

		$this->info('Seeding tenant database "'.$databaseName.'"...');

		$this->getSeeder()->run();
	}

	/**
	 * Get a seeder instance from the container.
	 *
	 * @return \Illuminate\Database\Seeder
	 */
	protected function getSeeder()
	{
		$class = $this->laravel->make($this->input->getOption('class'));

		return $class->setContainer($this->laravel)->setCommand($this);
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
		return [
			['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', 'DatabaseSeeder'],

			['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],

			['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
		];
	}

}

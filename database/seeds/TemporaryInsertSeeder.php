<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Core\Helpers\PermissionHelper;

class TemporaryInsertSeeder extends Seeder
{
    private $output;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

	/**
     * Run the database seeds.
     *
     * @return void
     */

	public function run()
    {
        $this->output->writeln('<info>--- Inserting export inventory value permission ---</info>');

        PermissionHelper::create('INVENTORY VALUE REPORT', ['export'], 'INVENTORY');

        $this->output->writeln('<info>--- Insert export inventory value permission finished ---</info>');

    }
}
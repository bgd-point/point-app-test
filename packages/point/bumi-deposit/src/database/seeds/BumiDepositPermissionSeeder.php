<?php

use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;

use Point\Core\Helpers\PermissionHelper;

class BumiDepositPermissionSeeder extends Seeder
{
    /**
     * @var Output
     */
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
        $this->output->writeln('<info>--- Permission Seeder Started ---</info>');

        $group = 'BUMI DEPOSIT';

        PermissionHelper::create('BUMI DEPOSIT', ['menu', 'create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI DEPOSIT BANK', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI DEPOSIT OWNER', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI DEPOSIT GROUP', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI DEPOSIT CATEGORY', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI DEPOSIT REPORT', ['read', 'export'], $group);
        $this->output->writeln('<info>updated bumi deposit permission</info>');

        $this->output->writeln('<info>--- Permission Seeder Finished ---</info>');
    }
}

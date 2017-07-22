<?php

use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;

class PointAccountingPermissionSeeder extends Seeder 
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

        $this->accounting(); $this->output->writeln('<info>updated accounting permission</info>');

        $this->output->writeln('<info>--- Permission Seeder Finished ---</info>');
    }

    private function accounting()
    {
        $group = 'POINT ACCOUNTING';

        PermissionHelper::create('POINT ACCOUNTING MEMO JOURNAL', ['create', 'read', 'update', 'delete', 'approval'], $group);

        PermissionHelper::create('POINT ACCOUNTING CUT OFF ACCOUNT', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT ACCOUNTING CUT OFF INVENTORY', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT ACCOUNTING CUT OFF RECEIVABLE', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT ACCOUNTING CUT OFF PAYABLE', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT ACCOUNTING CUT OFF FIXED ASSETS', ['create', 'read', 'update', 'delete', 'approval'], $group);
    }
}

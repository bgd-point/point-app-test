<?php

use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;

use Point\Core\Helpers\PermissionHelper;

class PointFinancePermissionSeeder extends Seeder
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

        $group = 'POINT FINANCE';

        PermissionHelper::create('POINT FINANCE CASHIER', ['menu'], $group);
        PermissionHelper::create('POINT FINANCE CASHIER CASH', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT FINANCE CASHIER BANK', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT FINANCE PAYMENT ORDER', ['menu','create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT FINANCE DEBTS AGING REPORT', ['read'], $group);
        PermissionHelper::create('POINT FINANCE CASH REPORT', ['read'], $group);
        PermissionHelper::create('POINT FINANCE BANK REPORT', ['read'], $group);

        $this->output->writeln('<info>--- Permission Seeder Finished ---</info>');
    }
}

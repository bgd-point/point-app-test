<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Core\Helpers\PermissionHelper;

class FrameworkPermissionTableSeeder extends Seeder
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

        $this->menu();
        $this->output->writeln('<info>updated menu permission</info>');
        $this->master();
        $this->output->writeln('<info>updated master permission</info>');
        $this->accounting();
        $this->output->writeln('<info>updated accounting permission</info>');
        $this->facility();
        $this->output->writeln('<info>updated facility permission</info>');
        $this->inventory();
        $this->output->writeln('<info>updated inventory permission</info>');

        $this->output->writeln('<info>attach role administrator to user default</info>');
        $this->output->writeln('<info>--- Permission Seeder Finished ---</info>');
    }

    private function menu()
    {
        $group = 'ACCESS MENU';

        PermissionHelper::create('INVENTORY', ['menu'], $group);
        PermissionHelper::create('PURCHASING', ['menu'], $group);
        PermissionHelper::create('SALES', ['menu'], $group);
        PermissionHelper::create('MANUFACTURE', ['menu'], $group);
        PermissionHelper::create('FINANCE', ['menu'], $group);
        PermissionHelper::create('ACCOUNTING', ['menu'], $group);
        PermissionHelper::create('EXPEDITION', ['menu'], $group);
    }

    private function master()
    {
        $group = 'MASTER';

        PermissionHelper::create('CONTACT', ['menu'], $group);
        PermissionHelper::create('COA', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('WAREHOUSE', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('ITEM', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('SUPPLIER', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('CUSTOMER', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('EMPLOYEE', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('EXPEDITION', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('ALLOCATION', ['create', 'read', 'update', 'delete', 'export'], $group);
        PermissionHelper::create('ALLOCATION REPORT', ['read', 'export'], $group);
        PermissionHelper::create('SERVICE', ['create', 'read', 'update', 'delete', 'export'], $group);
//        PermissionHelper::create('FIXED ASSETS ITEM', ['create', 'read', 'update', 'delete', 'export'], $group);
    }

    private function accounting()
    {
        $group = 'ACCOUNTING';

        PermissionHelper::create('BALANCE SHEET', ['read', 'export'], $group);
        PermissionHelper::create('TRIAL BALANCE', ['read', 'export'], $group);
        PermissionHelper::create('GENERAL LEDGER', ['read', 'export'], $group);
        PermissionHelper::create('SUB LEDGER', ['read', 'export'], $group);
        PermissionHelper::create('PROFIT AND LOSS', ['read', 'export'], $group);
        PermissionHelper::create('CASHFLOW', ['read', 'export'], $group);
    }

    private function facility()
    {
        $group = 'FACILITY';

        PermissionHelper::create('GLOBAL NOTIFICATION', ['manage'], $group);
    }

    private function inventory()
    {
        $group = 'INVENTORY';

        PermissionHelper::create('INVENTORY REPORT', ['read'], $group);
        PermissionHelper::create('INVENTORY VALUE REPORT', ['read'], $group);
    }
}

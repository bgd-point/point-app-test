<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Core\Helpers\PermissionHelper;

class PointSalesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
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

        $this->pos();

        $this->sales();

        $this->salesService();
    }

    private function pos()
    {
        $group = 'POINT SALES';

        PermissionHelper::create('POINT SALES POS', ['menu','create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT SALES POS PRICING', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT SALES POS REPORT', ['read', 'export'], $group);
        PermissionHelper::create('POINT SALES POS DAILY REPORT', ['read', 'export'], $group);
    }

    private function sales()
    {
        $group = 'POINT SALES';

        PermissionHelper::create('POINT SALES', ['menu'], $group);
        PermissionHelper::create('POINT SALES QUOTATION', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT SALES DOWNPAYMENT', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT SALES PAYMENT COLLECTION', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT SALES DELIVERY ORDER', ['create', 'read', 'update', 'delete','approval'], $group);
        PermissionHelper::create('POINT SALES ORDER', ['create', 'read', 'update', 'delete','approval'], $group);
        PermissionHelper::create('POINT SALES INVOICE', ['create', 'read', 'update', 'delete','approval'], $group);
        PermissionHelper::create('POINT SALES RETURN', ['create', 'read', 'update', 'delete','approval'], $group);
        PermissionHelper::create('POINT SALES REPORT', ['read', 'export'], $group);
    }

    private function salesService()
    {
        $group = 'POINT SALES SERVICE';

        PermissionHelper::create('POINT SALES SERVICE', ['menu'], $group);
        PermissionHelper::create('POINT SALES SERVICE INVOICE', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT SALES SERVICE DOWNPAYMENT', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT SALES SERVICE PAYMENT COLLECTION', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT SALES SERVICE REPORT', ['read', 'export'], $group);
    }
}

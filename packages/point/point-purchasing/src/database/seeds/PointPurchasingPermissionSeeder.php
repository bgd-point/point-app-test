<?php

use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Core\Models\Master\Role;
use Point\Core\Models\Master\Permission;

class PointPurchasingPermissionSeeder extends Seeder
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
        $this->basicPurchasing();
        $this->servicePurchasing();
        $this->fixedAssetsPurchasing();

        $this->output->writeln('<info>updated purchasing permission</info>');
    }

    private function basicPurchasing()
    {
        $group = 'POINT PURCHASING';

        PermissionHelper::create('POINT PURCHASING', ['menu'], $group);
        PermissionHelper::create('POINT PURCHASING ORDER', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING REQUISITION', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING DOWNPAYMENT', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING PAYMENT ORDER', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING GOODS RECEIVED', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT PURCHASING ORDER', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT PURCHASING INVOICE', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT PURCHASING RETURN', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING REPORT', ['read', 'export'], $group);
        PermissionHelper::create('POINT PURCHASING CASH ADVANCE', ['create', 'read', 'update', 'delete', 'approval'], $group);
    }

    private function servicePurchasing()
    {
        $group = 'POINT PURCHASING SERVICE';

        PermissionHelper::create('POINT PURCHASING SERVICE', ['menu'], $group);
        PermissionHelper::create('POINT PURCHASING SERVICE INVOICE', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING SERVICE DOWNPAYMENT', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING SERVICE PAYMENT ORDER', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING SERVICE REPORT', ['read', 'export'], $group);
    }

    private function fixedAssetsPurchasing()
    {
        $group = 'POINT PURCHASING FIXED ASSETS';
        
        PermissionHelper::create('POINT PURCHASING FIXED ASSETS', ['menu'], $group);
        PermissionHelper::create('POINT PURCHASING ORDER FIXED ASSETS', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING REQUISITION FIXED ASSETS', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING DOWNPAYMENT FIXED ASSETS', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING PAYMENT ORDER FIXED ASSETS', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING GOODS RECEIVED FIXED ASSETS', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING ORDER FIXED ASSETS', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT PURCHASING INVOICE FIXED ASSETS', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT PURCHASING RETURN FIXED ASSETS', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT PURCHASING CONTRACT', ['create', 'read', 'update', 'delete', 'approval'], $group);

    }
}

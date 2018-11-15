<?php

use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointPurchasingServicePurchaseOrderQuickSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = 'POINT PURCHASING SERVICE';
        PermissionHelper::create('POINT PURCHASING SERVICE ORDER', ['create', 'read', 'update', 'delete', 'approval'], $group);
        FormulirNumberHelper::create('point-purchasing-service-purchase-order', 'PURCHASING-SERVICE/PO/');
        // admin must manually grant himself this permission
    }
}

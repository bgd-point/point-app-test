<?php

use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;

class PointPurchasingServicePurchaseOrderPermission extends Seeder
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
        // admin must manually grant himself this permission
    }
}

<?php

namespace Point\PointInventory\Http\Controllers\InventoryUsage;

use App\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;

class InventoryUsageVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.inventory.usage');

        $view = view('app.index');
        $view->array_vesa = InventoryUsage::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.inventory.usage');

        $view = view('app.index');
        $view->array_vesa = InventoryUsage::getVesaRejectInventoryUsage();
        return $view;
    }
}

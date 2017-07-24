<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use App\Http\Controllers\Controller;
use Point\PointAccounting\Models\CutOffInventory;

class CutOffInventoryVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.accounting.cut.off.inventory');

        $view = view('app.index');
        $view->array_vesa = CutOffInventory::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.accounting.cut.off.inventory');

        $view = view('app.index');
        $view->array_vesa = CutOffInventory::getVesaReject();
        return $view;   
    }
}

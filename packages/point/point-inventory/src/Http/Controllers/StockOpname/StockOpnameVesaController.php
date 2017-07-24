<?php

namespace Point\PointInventory\Http\Controllers\StockOpname;

use App\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\PointInventory\Models\StockOpname\StockOpname;

class StockOpnameVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.inventory.stock.opname');

        $view = view('app.index');
        $view->array_vesa = StockOpname::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.inventory.stock.opname');

        $view = view('app.index');
        $view->array_vesa = StockOpname::getVesaRejectStockOpname();
        return $view;
    }
}

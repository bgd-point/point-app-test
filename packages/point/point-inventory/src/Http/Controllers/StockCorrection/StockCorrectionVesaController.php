<?php

namespace Point\PointInventory\Http\Controllers\StockCorrection;

use App\Http\Controllers\Controller;
use Point\PointInventory\Models\StockCorrection\StockCorrection;

class StockCorrectionVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.inventory.stock.correction');

        $view = view('app.index');
        $view->array_vesa = StockCorrection::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.inventory.stock.correction');

        $view = view('app.index');
        $view->array_vesa = StockCorrection::getVesaRejectStockCorrection();
        return $view;
    }
}

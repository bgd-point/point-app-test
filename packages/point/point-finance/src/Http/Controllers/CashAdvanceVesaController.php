<?php

namespace Point\PointFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointFinance\Models\CashAdvance;

class CashAdvanceVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('menu.point.finance.cashier');

        $view = view('app.index');
        $view->array_vesa = CashAdvance::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('menu.point.finance.cashier');

        $view = view('app.index');
        $view->array_vesa = CashAdvance::getVesaReject();
        return $view;
    }
}

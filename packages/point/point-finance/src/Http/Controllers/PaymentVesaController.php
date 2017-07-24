<?php

namespace Point\PointFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointFinance\Models\PaymentReference;

class PaymentVesaController extends Controller
{
    public function create()
    {
        access_is_allowed('menu.point.finance.cashier');

        $view = view('app.index');
        $view->array_vesa = PaymentReference::getVesaCreate();
        return $view;
    }
}

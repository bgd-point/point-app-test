<?php

namespace Point\PointFinance\Http\Controllers\PaymentOrder;

use App\Http\Controllers\Controller;
use Point\PointFinance\Models\PaymentOrder\PaymentOrder;

class PaymentOrderVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.finance.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.finance.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaReject();
        return $view;
    }
}

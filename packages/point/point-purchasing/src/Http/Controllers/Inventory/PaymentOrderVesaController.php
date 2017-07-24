<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Point\PointPurchasing\Models\Inventory\PaymentOrder;

class PaymentOrderVesaController extends Controller
{
    public function create()
    {
        access_is_allowed('create.point.purchasing.payment.oder');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaCreate();
        return $view;
    }

    public function approval()
    {
        access_is_allowed('approval.point.purchasing.payment.oder');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.purchasing.payment.oder');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaReject();
        return $view;
    }
}

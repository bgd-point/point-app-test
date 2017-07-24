<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Point\PointPurchasing\Models\Service\PaymentOrder;

class PaymentOrderVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.purchasing.service.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaApproval();
        return $view;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.service.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaCreate();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.purchasing.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaReject();
        return $view;
    }
}

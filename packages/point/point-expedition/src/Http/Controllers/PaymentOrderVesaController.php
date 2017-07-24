<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointExpedition\Models\PaymentOrder;

class PaymentOrderVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.expedition.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaCreate();
        return $view;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('approval.point.expedition.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.expedition.payment.order');

        $view = view('app.index');
        $view->array_vesa = PaymentOrder::getVesaReject();
        return $view;
    }
}

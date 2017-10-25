<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Point\PointSales\Models\Sales\DeliveryOrder;

class DeliveryOrderVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.sales.delivery.order');

        $view = view('app.index');
        $view->array_vesa = DeliveryOrder::getVesaCreate();
        return $view;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('approval.point.sales.delivery.order');

        $view = view('app.index');
        $view->array_vesa = DeliveryOrder::getVesaApproval();
        return $view;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rejected()
    {
        access_is_allowed('create.point.sales.delivery.order');

        $view = view('app.index');
        $view->array_vesa = DeliveryOrder::getVesaReject();
        return $view;
    }
}

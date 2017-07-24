<?php

namespace Point\PointPurchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\PointPurchasing\Models\PurchaseOrder;

class PurchaseOrderVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.purchasing.order');

        $view = view('app.index');
        $view->array_vesa = PurchaseOrder::getVesaApproval();
        return $view;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPurchaseOrder()
    {
        access_is_allowed('create.point.purchasing.order');

        $view = view('app.index');
        $view->array_vesa = PurchaseOrder::getVesaCreate();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.purchasing.order');

        $view = view('app.index');
        $view->array_vesa = PurchaseOrder::getVesaReject();
        return $view;
    }
    
    public function createDownpayment()
    {
        access_is_allowed('create.point.purchasing.downpayment');

        $view = view('app.index');
        $view->array_vesa = PurchaseOrder::getVesaCreateDownpayment();
        return $view;
    }
}

<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;

class PurchaseOrderVesaController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.order');

        $view = view('app.index');
        $view->array_vesa = PurchaseOrder::getVesaCreate();
        return $view;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('approval.point.purchasing.order');

        $view = view('app.index');
        $view->array_vesa = PurchaseOrder::getVesaApproval();
        return $view;
    }


    public function rejected()
    {
        access_is_allowed('update.point.purchasing.order');

        $view = view('app.index');
        $view->array_vesa = PurchaseOrder::getVesaReject();
        return $view;
    }
}

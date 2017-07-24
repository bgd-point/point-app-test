<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;

class GoodsReceivedVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.goods.received');

        $view = view('app.index');
        $view->array_vesa = GoodsReceived::getVesaCreate();
        return $view;
    }

    public function approval()
    {
        access_is_allowed('approval.point.purchasing.goods.received');

        $view = view('app.index');
        $view->array_vesa = GoodsReceived::getVesaApproval();
        return $view;
    }


    public function rejected()
    {
        access_is_allowed('update.point.purchasing.goods.received');

        $view = view('app.index');
        $view->array_vesa = GoodsReceived::getVesaReject();
        return $view;
    }
}

<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\PointPurchasing\Models\Inventory\Retur;

class ReturVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.purchasing.retur');

        $view = view('app.index');
        $view->array_vesa = Retur::getVesaApproval();
        return $view;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.retur');

        $view = view('app.index');
        $view->array_vesa = Retur::getVesaCreate();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.purchasing.retur');

        $view = view('app.index');
        $view->array_vesa = Retur::getVesaReject();
        return $view;
    }
}

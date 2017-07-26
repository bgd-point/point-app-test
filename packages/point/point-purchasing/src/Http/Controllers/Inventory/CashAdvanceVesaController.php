<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Point\PointPurchasing\Models\Inventory\CashAdvance;

class CashAdvanceVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.cash.advance');

        $view = view('app.index');
        $view->array_vesa = CashAdvance::getVesaCreate();
        return $view;
    }

    public function approval()
    {
        access_is_allowed('approval.point.purchasing.cash.advance');

        $view = view('app.index');
        $view->array_vesa = CashAdvance::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.purchasing.cash.advance');

        $view = view('app.index');
        $view->array_vesa = CashAdvance::getVesaReject();
        return $view;
    }
}

<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;

class PurchaseRequisitionVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('approval.point.purchasing.requisition');

        $view = view('app.index');
        $view->array_vesa = PurchaseRequisition::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.purchasing.requisition');

        $view = view('app.index');
        $view->array_vesa = PurchaseRequisition::getVesaReject();
        return $view;
    }
}

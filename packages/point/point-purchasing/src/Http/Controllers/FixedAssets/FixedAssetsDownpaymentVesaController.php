<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsDownpayment;

class FixedAssetsDownpaymentVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.purchasing.downpayment.fixed.assets');

        $view = view('app.index');
        $view->array_vesa = FixedAssetsDownpayment::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.purchasing.downpayment.fixed.assets');

        $view = view('app.index');
        $view->array_vesa = FixedAssetsDownpayment::getVesaReject();
        return $view;
    }

    public function createPayment()
    {
        access_is_allowed('menu.point.finance.cashier');

        $view = view('app.index');
        $view->array_vesa = FixedAssetsDownpayment::getVesaCreatePayment();
        return $view;
    }
}

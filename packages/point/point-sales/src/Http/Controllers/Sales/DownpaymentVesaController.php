<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Point\PointSales\Models\Sales\Downpayment;

class DownpaymentVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.sales.downpayment');

        $view = view('app.index');
        $view->array_vesa = Downpayment::getVesaApproval();
        return $view;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('update.point.sales.downpayment');

        $view = view('app.index');
        $view->array_vesa = Downpayment::getVesaCreate();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.sales.downpayment');

        $view = view('app.index');
        $view->array_vesa = Downpayment::getVesaReject();
        return $view;
    }

    public function createPayment()
    {
        access_is_allowed('menu.point.finance.cashier');

        $view = view('app.index');
        $view->array_vesa = Downpayment::getVesaCreatePayment();
        return $view;
    }
}

<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Point\PointSales\Models\Sales\SalesOrder;

class SalesOrderVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.sales.order');

        $view = view('app.index');
        $view->array_vesa = SalesOrder::getVesaCreate();
        return $view;
    }

    public function approval()
    {
        access_is_allowed('read.point.sales.order');

        $view = view('app.index');
        $view->array_vesa = SalesOrder::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.sales.order');

        $view = view('app.index');
        $view->array_vesa = SalesOrder::getVesaReject();
        return $view;
    }
}

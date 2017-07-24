<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Point\PointSales\Models\Sales\Retur;

class ReturVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.sales.return');

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
        access_is_allowed('create.point.sales.return');

        $view = view('app.index');
        $view->array_vesa = Retur::getVesaCreate();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.sales.return');

        $view = view('app.index');
        $view->array_vesa = Retur::getVesaReject();
        return $view;
    }
}

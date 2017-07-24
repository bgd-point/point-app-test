<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointExpedition\Models\Downpayment;

class DownpaymentVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.expedition.downpayment');

        $view = view('app.index');
        $view->array_vesa = Downpayment::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.expedition.downpayment');

        $view = view('app.index');
        $view->array_vesa = Downpayment::getVesaReject();
        return $view;
    }
}

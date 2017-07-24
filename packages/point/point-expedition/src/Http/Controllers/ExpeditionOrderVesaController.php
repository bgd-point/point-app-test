<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointExpedition\Models\ExpeditionOrder;

class ExpeditionOrderVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function create()
    {
        access_is_allowed('create.point.expedition.order');

        $view = view('app.index');
        $view->array_vesa = ExpeditionOrder::getVesaCreate();
        return $view;
    }
    
    public function approval()
    {
        access_is_allowed('approval.point.expedition.order');

        $view = view('app.index');
        $view->array_vesa = ExpeditionOrder::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.expedition.order');

        $view = view('app.index');
        $view->array_vesa = ExpeditionOrder::getVesaReject();
        return $view;
    }
}

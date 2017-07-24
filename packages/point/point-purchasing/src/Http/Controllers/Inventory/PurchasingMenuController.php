<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

class PurchasingMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('menu.purchasing');

        $view = view('point-purchasing::app.purchasing.point.inventory.menu');
        return $view;
    }
}

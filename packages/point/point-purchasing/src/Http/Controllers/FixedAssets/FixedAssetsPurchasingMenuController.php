<?php

namespace Point\PointPurchasing\Http\Controllers;

class FixedAssetsPurchasingMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // access_is_allowed('read.point.purchasing.requisition');
        $view = view('point-purchasing::app.purchasing.point.menu');
        return $view;
    }
}

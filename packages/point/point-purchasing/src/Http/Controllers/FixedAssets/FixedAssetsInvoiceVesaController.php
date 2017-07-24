<?php

namespace Point\PointPurchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;

class FixedAssetsInvoiceVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.invoice.fixed.assets');

        $view = view('app.index');
        $view->array_vesa = FixedAssetsInvoice::getVesaCreate();
        return $view;
    }

    public function createMasterFixedAssets()
    {
        access_is_allowed('create.point.purchasing.invoice.fixed.assets');

        $view = view('app.index');
        $view->array_vesa = FixedAssetsInvoice::getVesaCreateMasterFixedAssets();
        return $view;
    }
}

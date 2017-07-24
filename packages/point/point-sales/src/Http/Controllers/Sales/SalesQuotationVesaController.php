<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Point\PointSales\Models\Sales\SalesQuotation;

class SalesQuotationVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.sales.quotation');

        $view = view('app.index');
        $view->array_vesa = SalesQuotation::getVesaApproval();
        return $view;
    }
}

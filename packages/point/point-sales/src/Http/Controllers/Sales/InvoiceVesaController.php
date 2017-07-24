<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Point\PointSales\Models\Sales\Invoice;

class InvoiceVesaController extends Controller
{
    public function create()
    {
        access_is_allowed('create.point.sales.invoice');

        $view = view('app.index');
        $view->array_vesa = Invoice::getVesaCreate();
        return $view;
    }
}

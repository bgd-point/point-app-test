<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointExpedition\Models\Invoice;

class InvoiceVesaController extends Controller
{
    public function create()
    {
        access_is_allowed('create.point.expedition.invoice');

        $view = view('app.index');
        $view->array_vesa = Invoice::getVesaCreate();
        return $view;
    }
}

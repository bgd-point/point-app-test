<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Point\PointPurchasing\Helpers\ServiceInvoiceHelper;
use Point\PointPurchasing\Models\Service\Invoice;

class ServiceReportController extends Controller
{
    public function index()
    {
        $view = view('point-purchasing::app.purchasing.point.service.report');
        $list_invoice = Invoice::joinFormulir()
            ->joinPerson()
            ->joinDetailService()
            ->joinService()
            ->notArchived()
            ->groupBy('point_purchasing_service_invoice.id');

        $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, FALSE, FALSE, \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);

        return $view;
    }
}

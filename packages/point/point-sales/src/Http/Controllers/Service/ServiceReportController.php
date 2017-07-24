<?php

namespace Point\PointSales\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Point\PointSales\Helpers\ServiceInvoiceHelper;
use Point\PointSales\Models\Service\Invoice;

class ServiceReportController extends Controller
{
    public function index()
    {
        $view = view('point-sales::app.sales.point.service.report');
        $list_invoice = Invoice::joinFormulir()
            ->joinPerson()
            ->joinDetailService()
            ->joinService()
            ->notArchived()
            ->groupBy('point_sales_service_invoice.id');

        $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, 'point_sales_service_invoice.id', 'asc', \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }
}

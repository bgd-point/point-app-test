<?php

namespace Point\PointFinance\Http\Controllers;

use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\AllocationReport;

class AllocationReportController extends Controller
{
    public function index()
    {
        $view = view('point-finance::app.finance.point.allocation-report.index');

        $view->list_report = AllocationReport::joinFormulir()
            ->selectOriginal()->orderBy(\DB::raw('CAST(form_date as date)'), 'asc')
            ->orderBy('form_raw_number', 'desc')->get();

        $view->list_allocation = Allocation::all();

        return $view;
    }
}

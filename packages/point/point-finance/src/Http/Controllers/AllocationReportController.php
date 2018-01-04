<?php

namespace Point\PointFinance\Http\Controllers;

use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\AllocationReport;

class AllocationReportController extends Controller
{
    public function index()
    {
        access_is_allowed('read.allocation.report');

        $allocation_id = 0;
        $allocation_name = '';

        if (app('request')->get('allocation_id')) {
            $allocation_id = app('request')->get('allocation_id');
        }

        $view = view('point-finance::app.finance.point.allocation-report.index');
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');

        if ($allocation_id > 0) {
            $allocation_name = Allocation::find($allocation_id)->name;
        }

        $view->list_report = AllocationReport::joinFormulir()
            ->selectOriginal()->orderBy(\DB::raw('CAST(form_date as date)'), 'asc')
            ->whereBetween('formulir.form_date', [$view->date_from, $view->date_to])
            ->where('allocation_id', $allocation_id)
            ->orderBy('form_raw_number', 'desc')->get();

        $view->list_allocation = Allocation::all();
        $view->allocation_name = $allocation_name;

        return $view;
    }
}

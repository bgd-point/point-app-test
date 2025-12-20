<?php

namespace Point\PointFinance\Http\Controllers;

use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\AllocationReport;

class AllocationReportCashFlowController extends Controller
{
    public function index()
    {
        access_is_allowed('read.allocation.report');

        $allocation_id = 0;
        $allocation_name = '';

        if (app('request')->get('allocation_id')) {
            $allocation_id = app('request')->get('allocation_id');
        }

        $view = view('point-finance::app.finance.point.allocation-report-cash-flow.index');
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');

        if ($allocation_id > 0) {
            $allocation_name = Allocation::find($allocation_id)->name;
        }

        $view->list_report = AllocationReport::joinFormulir()
            ->selectOriginal()->orderBy(\DB::raw('CAST(form_date as date)'), 'asc')
            ->whereBetween('formulir.form_date', [$view->date_from, $view->date_to])
            ->where('allocation_id', $allocation_id)
            ->where(function ($query) {
                $query->where('formulir.form_number', 'like', 'BANK-IN/%')
                ->orWhere('formulir.form_number', 'like', 'BANK-OUT/%')
                ->orWhere('formulir.form_number', 'like', 'CASH-IN/%')
                ->orWhere('formulir.form_number', 'like', 'CASH-OUT/%')
                ->orWhere('formulir.form_number', 'like', 'IU/%');
            })
            ->orderBy('form_raw_number', 'desc')->get();

        $view->list_allocation = Allocation::all();
        $view->allocation_name = $allocation_name;

        return $view;
    }

    public function export()
    {
        $file_name = 'Allocation Report '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        $allocation_id = \Input::get('allocation_id');
        $list_report = AllocationReport::joinFormulir()
            ->selectOriginal()->orderBy(\DB::raw('CAST(form_date as date)'), 'asc')
            ->whereBetween('formulir.form_date', [$date_from, $date_to])
            ->where('allocation_id', $allocation_id)
            ->where(function ($query) {
                $query->where('formulir.form_number', 'like', 'BANK-IN/%')
                ->orWhere('formulir.form_number', 'like', 'BANK-OUT/%')
                ->orWhere('formulir.form_number', 'like', 'CASH-IN/%')
                ->orWhere('formulir.form_number', 'like', 'CASH-OUT/%')
                ->orWhere('formulir.form_number', 'like', 'IU/%');
            })
            ->orderBy('form_raw_number', 'desc')->get();

        \Excel::create($file_name, function ($excel) use ($date_from, $date_to, $allocation_id, $list_report) {
            $excel->sheet('Allocation Report', function ($sheet) use ($date_from, $date_to, $allocation_id, $list_report) {
                $data = array(
                    'allocation' => Allocation::find($allocation_id),
                    'list_report' => $list_report,
                    'date_to' => $date_to,
                    'date_from' => $date_from
                );
                $sheet->setColumnFormat(array(
                    'D' => '#,##0.00',
                    'E' => '#,##0.00'
                ));
                $sheet->loadView('point-finance::app.finance.point.allocation-report-cash-flow.export', $data);
                $sheet->setColumnFormat(array(
                    'D' => '#,##0.00',
                    'E' => '#,##0.00'
                ));
            });
        })->export('xls');
    }
}

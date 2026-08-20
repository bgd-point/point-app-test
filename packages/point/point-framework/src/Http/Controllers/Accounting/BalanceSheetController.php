<?php

namespace Point\Framework\Http\Controllers\Accounting;

use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\CoaSaldo;
use Point\Framework\Models\Master\CoaPosition;

class BalanceSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date_from = '2000-01-01 00:00:00';

        $default_date = \Carbon::now()->subMonth();

        $month_to = request('month_to', $default_date->month);
        $year_to = request('year_to', $default_date->year);

        if ($month_to == date('n') && $year_to == date('Y')) {
            gritter_error('cannot select current month and year');
            return back()->withErrors([
                'date_to' => 'The selected month and year cannot be the current month and year.',
            ]);
        }

        $view = view('framework::app.accounting.balance-sheet.index');

        $view->coa_asset = CoaPosition::find(1);
        $view->coa_liability = CoaPosition::find(2);
        $view->coa_equity = CoaPosition::find(3);

        $view->total_asset = 0;
        $view->total_liability = 0;
        $view->total_equity = 0;

        $view->date_from = $date_from;

        $view->month = [
            'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
            'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'
        ];

        $view->month_to = $month_to;
        $view->year_to = $year_to;

        $view->date_to = date(
            'Y-m-t 23:59:59',
            strtotime($year_to . '-' . $month_to . '-01')
        );

        return $view;
    }

    public function export()
    {
        $file_name = 'Balance Sheet '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = '2000-01-01 00:00:00';
        $date_to = (app('request')->input('month_to') && app('request')->input('year_to'))
            ? date(
                'Y-m-t 23:59:59',
                strtotime(
                    app('request')->input('year_to') . '-' . app('request')->input('month_to') . '-01'
                )
            )
            : date('Y-m-t 23:59:59');

        $month_to = request('month_to');
        $year_to = request('year_to');

        if ($month_to == date('n') && $year_to == date('Y')) {
            return back()->withErrors([
                'date_to' => 'The selected month and year cannot be the current month and year.',
            ]);
        }

        \Excel::create($file_name, function ($excel) use ($date_from, $date_to) {
            $excel->sheet('Balance Sheet', function ($sheet) use ($date_from, $date_to) {
                $data = array(
                    'coa_asset' => CoaPosition::find(1),
                    'coa_liability' => CoaPosition::find(2),
                    'coa_equity' => CoaPosition::find(3),
                    'total_asset' => 0,
                    'total_liability' => 0,
                    'total_equity' => 0,
                    'date_to' => $date_to,
                    'date_from' => $date_from
                 );
                
                $sheet->loadView('framework::app.accounting.balance-sheet._data', $data);
            });
        })->export('xls');
    }
}

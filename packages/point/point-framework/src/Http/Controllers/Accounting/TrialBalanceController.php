<?php

namespace Point\Framework\Http\Controllers\Accounting;

use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\CoaSaldo;
use Point\Framework\Models\Master\CoaPosition;

class TrialBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date_from = date('Y-m-01 00:00:00');
        $date_to = date('Y-m-d 23:59:59');
        $view = view('framework::app.accounting.trial-balance.index');
        $view->coa_asset = CoaPosition::find(1);
        $view->coa_liability = CoaPosition::find(2);
        $view->coa_equity = CoaPosition::find(3);
        $view->coa_revenue = CoaPosition::find(4);
        $view->coa_expense = CoaPosition::find(5);
        $view->total_debit = 0;
        $view->total_credit = 0;
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : $date_from;
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : $date_to;
        return $view;
    }

    public function export()
    {
        $file_name = 'Trial Balance '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        \Excel::create($file_name, function($excel) use ($date_from, $date_to) {

            $excel->sheet('Trial Balance', function($sheet) use ($date_from, $date_to) {
                $data = array(
                    'coa_asset' => CoaPosition::find(1),
                    'coa_liability' => CoaPosition::find(2),
                    'coa_equity' => CoaPosition::find(3),
                    'coa_revenue' => CoaPosition::find(4),
                    'coa_expense' => CoaPosition::find(5),
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'date_to' => $date_to,
                    'date_from' => $date_from
                 );
                
                $sheet->loadView('framework::app.accounting.trial-balance._data', $data);

            });

        })->export('xls');
    }
}

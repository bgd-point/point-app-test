<?php

namespace Point\Framework\Http\Controllers\Accounting;

use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\CoaSaldo;
use Point\Framework\Models\Master\Coa;

class CashFlowController extends Controller
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
        $view = view('framework::app.accounting.cashflow.index');

        $view->list_coa_operations = Coa::where('coa_cash_flow_id', '=', 1)->get();
        $view->list_coa_investment = Coa::where('coa_cash_flow_id', '=', 2)->get();
        $view->list_coa_financing = Coa::where('coa_cash_flow_id', '=', 3)->get();
        $view->cashflow_operations = 0;
        $view->cashflow_investment = 0;
        $view->cashflow_financing = 0;
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : $date_from;
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : $date_to;
        return $view;
    }

    public function export()
    {
        $file_name = 'Cashflow '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        \Excel::create($file_name, function($excel) use ($date_from, $date_to) {
            $excel->sheet('Cashflow', function($sheet) use ($date_from, $date_to) {
                $data = array(
                    'list_coa_operations' => Coa::where('coa_cash_flow_id', '=', 1)->get(),
                    'list_coa_investment' => Coa::where('coa_cash_flow_id', '=', 2)->get(),
                    'list_coa_financing' => Coa::where('coa_cash_flow_id', '=', 3)->get(),
                    'cashflow_operations' => 0,
                    'cashflow_investment' => 0,
                    'cashflow_financing' => 0,
                    'date_to' => $date_to,
                    'date_from' => $date_from
                 );
                
                $sheet->loadView('framework::app.accounting.cashflow._data', $data);

            });

        })->export('xls');
    }
}

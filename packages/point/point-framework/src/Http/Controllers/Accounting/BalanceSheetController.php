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
        $date_to = date('Y-m-d 23:59:59');
        $view = view('framework::app.accounting.balance-sheet.index');
        $view->coa_asset = CoaPosition::find(1);
        $view->coa_liability = CoaPosition::find(2);
        $view->coa_equity = CoaPosition::find(3);
        $view->total_asset = 0;
        $view->total_liability = 0;
        $view->total_equity = 0;
        $view->date_from = $date_from;
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : $date_to;
        return $view;
    }

    public function export()
    {
        $file_name = 'Balance Sheet '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = '2000-01-01 00:00:00';
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
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

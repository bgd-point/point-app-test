<?php

namespace Point\Framework\Http\Controllers\Accounting;

use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\CoaSaldo;
use Point\Framework\Models\Master\Coa;
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
        $view->list_coa = Coa::active()->orderBy('coa_number')->orderBy('name')->get();
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : $date_from;
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : $date_to;
        $view->export = false;
        return $view;
    }

    public function export()
    {
        $file_name = 'Trial Balance '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        \Excel::create($file_name, function ($excel) use ($date_from, $date_to) {
            $excel->sheet('Trial Balance', function ($sheet) use ($date_from, $date_to) {
                $data = array(
                    'list_coa' => Coa::active()->orderBy('coa_number')->orderBy('name')->get(),
                    'date_to' => $date_to,
                    'date_from' => $date_from,
                    'export' => true
                 );
                
                $sheet->loadView('framework::app.accounting.trial-balance._data', $data);
            });
        })->export('xls');
    }
}

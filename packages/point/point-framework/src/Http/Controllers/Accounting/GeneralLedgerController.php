<?php

namespace Point\Framework\Http\Controllers\Accounting;

use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\CoaSaldo;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;

class GeneralLedgerController extends Controller
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
        $view = view('framework::app.accounting.general-ledger.index');
        $view->list_coa = Coa::where('id', '>', 0)->orderBy('coa_number')->orderBy('name')->get();
        $view->coa_id = \Input::get('coa_filter') ?: 0;
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : $date_from;
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : $date_to;

        $view->journals = [];
        if ($view->coa_id > 0) {
            $view->journals = Journal::where('coa_id', '=', $view->coa_id)
                ->where('form_date', '>=', $view->date_from)
                ->where('form_date', '<=', $view->date_to)
                ->get();
        }
        return $view;
    }

    public function export()
    {
        $file_name = 'General Ledger '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        $coa_id = \Input::get('coa_filter') ?: 0;
        $journals = [];
        if ($coa_id > 0) {
            $journals = Journal::where('coa_id', '=', $coa_id)
                ->where('form_date', '>=', $date_from)
                ->where('form_date', '<=', $date_to)
                ->get();
        }

        \Excel::create($file_name, function($excel) use ($date_from, $date_to, $coa_id, $journals) {

            $excel->sheet('General Ledger', function($sheet) use ($date_from, $date_to, $coa_id, $journals) {
                $data = array(
                    'list_coa' => Coa::where('id', '>', 0)->orderBy('coa_number')->orderBy('name')->get(),
                    'coa_id' => $coa_id,
                    'journals' => $journals,
                    'date_to' => $date_to,
                    'date_from' => $date_from
                 );
                
                $sheet->loadView('framework::app.accounting.general-ledger._data', $data);

            });

        })->export('xls');
    }
}

<?php

namespace Point\PointFinance\Http\Controllers;

use Point\Core\Traits\ValidationTrait;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Person;

class DebtsAgingReportController extends Controller
{
    use ValidationTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report()
    {
        $view = view('point-finance::app.finance.point.debts-aging-report.index');
        $view->list_coa = Coa::where('subledger_type', get_class(new Person))
            ->selectOriginal()
            ->get();

        $view->list_person = Person::active()->get();
        $view->date = date('Y-m-d');

        return $view;
    }

    public function _view()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $subledger_id = \Input::get('subledger_id') ? : 0;
        $date = \Input::get('date');
        $report = AccountPayableAndReceivable::where('account_id', \Input::get('coa_id'))
            ->where('form_date', '<=', date_format_db($date, 'end'))
            ->where(function ($query) use ($subledger_id) {
                if ($subledger_id) {
                    $query->where('person_id', $subledger_id);
                }
            })
            ->get();

        $view = view('point-finance::app.finance.point.debts-aging-report._detail');
        $view->list_report = $report;
        $view->date = date_format_db($date, 'end');
        return $view;
    }

    public function export()
    {
        $file_name = 'Debts Aging Report '.auth()->user()->id . '' . date('Y-m-d_His');
        $date = \Input::get('date');
        $coa_id = \Input::get('coa_id');
        $subledger_id = \Input::get('subledger_id') ? : 0;
        $report = AccountPayableAndReceivable::where('account_id', $coa_id)
            ->where('form_date', '<=', date_format_db($date, 'end'))
            ->where(function ($query) use ($subledger_id) {
                if ($subledger_id) {
                    $query->where('person_id', $subledger_id);
                }
            })
            ->get();
        \Log::info($report);
        \Excel::create($file_name, function ($excel) use ($date,$subledger_id,$report) {
            $excel->sheet('Debts Aging Report', function ($sheet) use ($date,$subledger_id,$report) {
                $data = array(
                    'list_report' => $report,
                    'date' => date_format_db($date, 'end')
                );

                $sheet->loadView('point-finance::app.finance.point.debts-aging-report._detail', $data);
            });
        })->export('xls');
    }
}

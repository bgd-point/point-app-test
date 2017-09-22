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

        return $view;
    }

    public function _view()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $subledger_id = \Input::get('subledger_id') ? : 0;
        $report = AccountPayableAndReceivable::where('done', 0)
            ->where('account_id', \Input::get('coa_id'))
            ->where(function ($query) use ($subledger_id) {
                if ($subledger_id) {
                    $query->where('person_id', $subledger_id);
                }
            })
            ->get();

        $view = view('point-finance::app.finance.point.debts-aging-report._detail');
        $view->list_report = $report;
        return $view;
    }
}

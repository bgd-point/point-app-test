<?php

namespace Point\Framework\Http\Controllers\Accounting;

use DateTime;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\CoaSaldo;
use Point\Framework\Models\Master\Coa;

class ProfitAndLossController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date_from = (app('request')->input('month_from') && app('request')->input('year_from'))
            ? date(app('request')->input('year_from').'-'.app('request')->input('month_from').'-01 00:00:00')
            : date('Y-m-01 00:00:00');

        $date_to = (app('request')->input('month_to') && app('request')->input('year_to'))
            ? date(app('request')->input('year_to').'-'.app('request')->input('month_to').'-10 00:00:00')
            : date('Y-m-01 00:00:00');

        $datetime_from = new DateTime($date_from);
        $datetime_to = new DateTime($date_to);
        $diff = $datetime_from->diff($datetime_to);
        $total_month = ($diff->format('%y') * 12) + $diff->format('%m');

        if ($total_month > 12) {
            gritter_error('maximum 12 month');
            return redirect()->back();
        }

        $view = view('framework::app.accounting.profit-and-loss.index');
        $view->month = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $view->total_month = $total_month;
        $view->list_coa_revenue = Coa::join('coa_category', 'coa_category.id', '=', 'coa.coa_category_id')
            ->where('coa_category.coa_position_id', '=', 4)
            ->active()
            ->orderBy('coa.coa_number')
            ->select('coa.*')
            ->get();
        $view->list_coa_expense = Coa::join('coa_category', 'coa_category.id', '=', 'coa.coa_category_id')
            ->where('coa_category.coa_position_id', '=', 5)
            ->active()
            ->orderBy('coa.coa_number')
            ->select('coa.*')
            ->get();
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : $date_from;
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : $date_to;
        return $view;
    }
}

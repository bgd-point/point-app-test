<?php

namespace Point\PointFinance\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Coa;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\Cash\Cash;
use Point\PointFinance\Models\Cash\CashCashAdvance;
use Point\PointFinance\Models\CashAdvance;
use Point\PointFinance\Models\PaymentReference;
use Point\PointPurchasing\Helpers\CashAdvanceHelper;

class CashAdvanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('point-finance::app.finance.point.cash-advance.index');

        $list_cash_advance = CashAdvance::joinFormulir()->joinEmployee()->notArchived()->selectOriginal()->orderByStandard();
        $list_cash_advance = CashAdvanceHelper::searchList($list_cash_advance, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));

        $view->list_cash_advance = $list_cash_advance->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $view = view('point-finance::app.finance.point.cash-advance.create');

        $view->list_user_approval = UserHelper::getAllUser();

        $view->list_employee = PersonHelper::getByType(['employee']);

        $view->list_cash_account = Coa::active()
            ->joinCategory()
            ->where('coa_category.name', 'Petty Cash')->orWhere('coa_category.name', 'Bank Account')
            ->selectOriginal()->orderBy('coa_number')->orderBy('name')->get();

        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'employee_id' => 'required',
            'amount' => 'required|min:1',
            'approval_to' => 'required',
        ]);

        FormulirHelper::isAllowedToCreate('create.point.finance.cash.advance', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-finance-cash-advance');

        $cash_advance = new CashAdvance;
        $cash_advance->formulir_id = $formulir->id;
        $cash_advance->payment_type = $request->input('payment_type');
        if ($cash_advance->payment_type == 'cash') {
            $cash_advance->coa_id = $request->input('coa_cash_id');
        } else {
            $cash_advance->coa_id = $request->input('coa_bank_id');
        }

        $cash_advance->employee_id = $request->input('employee_id');
        $cash_advance->amount = number_format_db($request->input('amount'));
        $cash_advance->remaining_amount = number_format_db($request->input('amount'));
        $cash_advance->is_payed = false;
        $cash_advance->save();

        timeline_publish('create.cash.advance', 'create cash advance ' . $cash_advance->formulir->form_number . ' success');

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('finance/point/cash-advance/' . $cash_advance->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-finance::app.finance.point.cash-advance.show');

        $view->cash_advance = CashAdvance::find($id);

        $view->list_cash_advance_archived = CashAdvance::joinFormulir()
            ->archived($view->cash_advance->formulir->form_number)
            ->get();

        $view->revision = $view->list_cash_advance_archived->count();

        $view->list_referenced = CashCashAdvance::where('cash_advance_id', '=', $view->cash_advance->id)->get();

        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.finance.cash.advance');

        $view = view('point-finance::app.finance.point.cash-advance.archived');
        $view->cash_advance_archived = CashAdvance::find($id);
        $view->cash_advance = CashAdvance::joinFormulir()->notArchived($view->cash_advance_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $view = view('point-finance::app.finance.point.cash-advance.edit');

        $view->list_user_approval = UserHelper::getAllUser();

        $view->list_cash_account = Coa::active()
            ->joinCategory()
            ->where('coa_category.name', 'Petty Cash')->orWhere('coa_category.name', 'Bank Account')
            ->selectOriginal()->orderBy('coa_number')->orderBy('name')->get();

        $view->list_employee = PersonHelper::getByType(['employee']);

        $view->cash_advance = CashAdvance::find($id);

        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'employee_id' => 'required',
            'edit_notes' => 'required',
            'amount' => 'required|min:1',
            'approval_to' => 'required',
        ]);

        $cash_advance = CashAdvance::find($id);

        FormulirHelper::isAllowedToUpdate('update.point.finance.cash.advance', date_format_db($request->input('form_date')), $cash_advance->formulir);

        DB::beginTransaction();

        PaymentReference::where('payment_reference_id', $cash_advance->formulir_id)->delete();

        $formulir_old = FormulirHelper::archive($request->input(), $cash_advance->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);

        $cash_advance = new CashAdvance;
        $cash_advance->formulir_id = $formulir->id;
        $cash_advance->employee_id = $request->input('employee_id');
        $cash_advance->amount = number_format_db($request->input('amount'));
        $cash_advance->remaining_amount = number_format_db($request->input('amount'));
        $cash_advance->payment_type = $request->input('payment_type');
        if ($cash_advance->payment_type == 'cash') {
            $cash_advance->coa_id = $request->input('coa_cash_id');
        } else {
            $cash_advance->coa_id = $request->input('coa_bank_id');
        }
        $cash_advance->is_payed = false;
        $cash_advance->save();

        timeline_publish('update.cash.advance', 'update advance ' . $cash_advance->formulir->form_number . ' success');

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('finance/point/cash-advance/' . $cash_advance->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }

    public function _list(Request $request)
    {
        return response()->json(array(
            'lists' => CashAdvance::joinFormulir()->joinEmployee()->notArchived()->notCanceled()->selectOriginal()
                ->where('is_payed', true)
                ->where('form_date', '<', date_format_db($request->get('date'), 'end'))
                ->where('payment_type', $request->get('type'))
                ->where('coa_id', $request->get('account'))
                ->where('remaining_amount', '>', 0)
                ->handedOver()
                ->select('point_finance_cash_advance.id as value', DB::raw('CONCAT(formulir.form_number, " - ", remaining_amount, " a/n ",person.name) AS text'))
                ->get()
                ->toArray()
        ));
    }

    public function handOver($id)
    {
        $cashAdvance = CashAdvance::find($id);
        $cashAdvance->handed_over = true;
        $cashAdvance->save();

        $cashAdvance->formulir->form_date = \Carbon::now();
        $cashAdvance->formulir->save();

        return redirect()->back();
    }
}

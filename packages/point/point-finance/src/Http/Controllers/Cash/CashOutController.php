<?php

namespace Point\PointFinance\Http\Controllers\Cash;

use Illuminate\Auth\id;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Helpers\PaymentHelper;
use Point\PointFinance\Models\Cash\Cash;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Person;
use Point\PointFinance\Models\CashAdvance;
use Point\PointFinance\Models\PaymentReference;

class CashOutController extends Controller
{
    use ValidationTrait;

    public function create($payment_reference_id)
    {
        access_is_allowed('create.point.finance.cashier.cash');

        $payment_reference = PaymentReference::find($payment_reference_id);

        $view = view('point-finance::app.finance.point.cash.out.create');
        $view->person = Person::find($payment_reference->person_id);
        $view->payment_reference = $payment_reference;
        $view->pay_to = $payment_reference->person_id;
        $view->list_coa = Coa::where('coa_category_id', 1)->active()->get();

        return $view;
    }

    public function choosePayable()
    {
        access_is_allowed('create.point.finance.cashier.cash');

        $view = view('point-finance::app.finance.point.cash.out.choose-payable');
        $view->payment_references = PaymentHelper::searchAvailablePayableReference('cash');
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
        $request['form_date'] = app('request')->input('payment_date');

        $payment_reference = PaymentReference::find($request->input('payment_reference_id'));
        formulir_is_allowed_to_create('create.point.finance.cashier.cash', date_format_db($request->input('form_date'), $request->input('time')), [$payment_reference->payment_reference_id]);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-finance-cash-payment-out');

        PaymentHelper::cashOut($formulir);

        timeline_publish('create.cash.payment', 'payment '  . $formulir->form_number. ' success');

        DB::commit();

        gritter_success('payment '.$formulir->form_number .' success', 'false');
        return redirect('finance/point/cash');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-finance::app.finance.point.cash.out.show');
        $view->cash = Cash::find($id);
        $view->list_cash_archived = Cash::joinFormulir()->archived($view->cash->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cash_archived->count();
        return $view;
    }

    /**
     * @param $formulir_id
     * @param $user_id
     *
     * @return $this
     * @throws \Point\Core\Exceptions\PointException
     */
    public function setApprovalTo ($cash_id, $user_id) {
        $cash = Cash::find($cash_id)->joinFormulir();
        $formulir_id = $cash->formulir_id;
        $formulir = Formulir::find($formulir_id);
        $formulir->approval_to = $user_id;
        $formulir->save();
    }
}

<?php

namespace Point\PointFinance\Http\Controllers\Cheque;

use Illuminate\Auth\id;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\MasterBank;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Helpers\PaymentHelper;
use Point\PointFinance\Models\Cheque\Cheque;
use Point\PointFinance\Models\PaymentReference;

class ChequeOutController extends Controller
{
    use ValidationTrait;

    public function create($payment_reference_id)
    {
        access_is_allowed('create.point.finance.cashier.cheque');

        $payment_reference = PaymentReference::find($payment_reference_id);

        $view = view('point-finance::app.finance.point.cheque.out.create');
        $view->person = Person::find($payment_reference->person_id);
        $view->payment_reference = $payment_reference;
        $view->pay_to = $payment_reference->person_id;
        $view->list_coa = Coa::where('coa_category_id', 9)->active()->get();
        $view->list_bank = MasterBank::all();


        return $view;
    }

    public function choosePayable()
    {
        access_is_allowed('create.point.finance.cashier.cheque');

        $view = view('point-finance::app.finance.point.cheque.out.choose-payable');
        $view->payment_references = PaymentHelper::searchAvailablePayableReference('cheque');
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
        $total_cheque = number_format_db(app('request')->input('total_cheque'));
        $total_payment = number_format_db(app('request')->input('total'));
        if ($total_cheque != $total_payment) {
            gritter_error('Failed, total not match', 'false');
            return redirect()->back();
        }

        $request['form_date'] = app('request')->input('payment_date');

        $payment_reference = PaymentReference::find($request->input('payment_reference_id'));
        formulir_is_allowed_to_create('create.point.finance.cashier.cheque', date_format_db($request->input('form_date'), $request->input('time')), [$payment_reference->payment_reference_id]);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-finance-cheque-payment-out');
        PaymentHelper::chequeOut($formulir);
        timeline_publish('create.cheque.payment', 'payment '  . $formulir->form_number. ' success');

        DB::commit();

        gritter_success('payment '.$formulir->form_number .' success', 'false');
        return redirect('finance/point/cheque');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-finance::app.finance.point.cheque.out.show');
        $view->cheque = Cheque::find($id);
        $view->list_cheque_archived = Cheque::joinFormulir()->archived($view->cheque->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cheque_archived->count();
        return $view;
    }
}

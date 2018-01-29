<?php

namespace Point\PointFinance\Http\Controllers\Bank;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\Master\CoaPosition;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Helpers\PaymentHelper;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\PaymentReference;

class BankInController extends Controller
{
    use ValidationTrait;

    public function createFromReference($payment_reference_id)
    {
        access_is_allowed('create.point.finance.cashier.bank');

        $payment_reference = PaymentReference::find($payment_reference_id);

        $view = view('point-finance::app.finance.point.bank.in.payment-reference.create');
        $view->person = Person::find($payment_reference->person_id);
        $view->payment_reference = $payment_reference;
        $view->pay_to = $payment_reference->person_id;
        $view->list_coa = Coa::where('coa_category_id', 2)->active()->get();

        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFromReference(Request $request)
    {
        $request['form_date'] = app('request')->input('payment_date');

        $payment_reference = PaymentReference::find($request->input('payment_reference_id'));
        formulir_is_allowed_to_create('create.point.finance.cashier.bank', date_format_db($request->input('form_date'), $request->input('time')), [$payment_reference->payment_reference_id]);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-finance-bank-payment-in');
        PaymentHelper::bankIn($formulir);
        timeline_publish('create.bank.payment', 'payment '  . $formulir->form_number. ' success');

        DB::commit();

        gritter_success('payment '.$formulir->form_number .' success', 'false');
        return redirect('finance/point/bank');
    }

    public function create()
    {
        access_is_allowed('create.point.finance.cashier.bank');

        $view = view('point-finance::app.finance.point.bank.in.create');
        $view->list_person = Person::active()->get();
        $view->list_allocation = Allocation::active()->get();
        $view->list_bank_account = Coa::where('coa_category_id', 2)->active()->get();
        $view->list_coa = Coa::getNonSubledgerAndNotInSettingJournal();

        $view->coa_revenue = CoaPosition::where("name", "Revenue")->first();
        $view->list_coa_category_revenue = CoaCategory::where('coa_position_id', $view->coa_revenue->id)->get();

        return $view;
    }

    public function chooseReceivable()
    {
        access_is_allowed('create.point.finance.cashier.bank');

        $view = view('point-finance::app.finance.point.bank.in.choose-receivable');
        $view->payment_references = PaymentHelper::searchAvailableReceivableReference('bank');
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
            'payment_date' => 'required',
            'person_id' => 'required',
        ]);

        $request['form_date'] = app('request')->input('payment_date');

        formulir_is_allowed_to_create('create.point.finance.cashier.bank', date_format_db($request->input('form_date'), $request->input('time')), $request->input('formulir_id'));

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-finance-bank-payment-in');
        PaymentHelper::bankIn($formulir);
        timeline_publish('create.bank.payment', 'receive payment '  . $formulir->form_number .' success');

        DB::commit();

        gritter_success('receive payment '.$formulir->form_number .' success', 'false');
        return redirect('finance/point/bank');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-finance::app.finance.point.bank.in.show');
        $view->bank = Bank::find($id);
        $view->list_bank_archived = Bank::joinFormulir()->archived($view->bank->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_bank_archived->count();
        return $view;
    }
}

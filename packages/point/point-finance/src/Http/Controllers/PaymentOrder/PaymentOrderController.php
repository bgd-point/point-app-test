<?php

namespace Point\PointFinance\Http\Controllers\PaymentOrder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\Master\CoaPosition;
use Point\Framework\Models\Master\Person;
use Point\PointFinance\Helpers\PaymentHelper;
use Point\PointFinance\Helpers\PaymentOrderHelper;
use Point\PointFinance\Http\Controllers\Controller;
use Point\PointFinance\Models\CashAdvance;
use Point\PointFinance\Models\PaymentOrder\PaymentOrder;

class PaymentOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.finance.payment.order');

        $view = view('point-finance::app.finance.point.payment-order.index');
        $list_payment_order = PaymentOrder::joinFormulir()->notArchived()->selectOriginal();
        $view->list_payment_order = PaymentOrderHelper::searchList($list_payment_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_payment_order = $list_payment_order->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        access_is_allowed('create.point.finance.payment.order');

        $view = view('point-finance::app.finance.point.payment-order.create-step-1');
        $view->list_person = Person::active()->get();
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep2()
    {
        access_is_allowed('create.point.finance.payment.order');

        $view = view('point-finance::app.finance.point.payment-order.create-step-2');
        if (app('request')->input('person_id') == null) {
            gritter_error('please choose who will receive this payment');
            return back();
        }
        $view->person = Person::find(app('request')->input('person_id'));
        $view->list_coa = Coa::getNonSubledgerAndNotInSettingJournal();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_cash_advance = CashAdvance::joinFormulir()->notArchived()->notCanceled()->selectOriginal()
            ->where('is_payed', true)
            ->where('remaining_amount', '>', 0)
            ->get();

        $view->coa_expense = CoaPosition::where("name", "Expense")->first();
        $view->list_coa_category_expense = CoaCategory::where('coa_position_id', $view->coa_expense->id)->get();

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep3()
    {
        access_is_allowed('create.point.finance.payment.order');

        if (app('request')->input('approval_to') == null) {
            gritter_error('please choose user approval');
            return redirect('finance/point/payment-order/create-step-1');
        }

        $view = view('point-finance::app.finance.point.payment-order.create-step-3');
        $view->payment_date = date_format_db(app('request')->input('payment_date'), app('request')->input('time'));
        $view->approval_to = User::find(app('request')->input('approval_to'));
        $view->person = Person::find(app('request')->input('person_id'));
        $view->coa_id = app('request')->input('coa_id');
        $view->payment_type = app('request')->input('payment_type');
        $view->cash_advance = CashAdvance::find(app('request')->input('cash_advance'));
        $view->notes = app('request')->input('notes');
        $view->amount = number_format_db(app('request')->input('amount'));
        $view->allocation_id = app('request')->input('allocation_id');
        $view->detail_notes = app('request')->input('detail_notes');
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
            'approval_to' => 'required',
            'person_id' => 'required',
            'payment_type' => 'required',
            'coa_id' => 'required',
            'coa_allocation_id' => 'required',
            'other_notes' => 'required',
            'coa_value' => 'required',
        ]);

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));

        DB::beginTransaction();

        formulir_is_allowed_to_create('create.point.finance.payment.order', date_format_db($request->input('form_date')), []);
        $formulir = FormulirHelper::create($request->input(), 'point-finance-payment-order');
        $payment_order = PaymentOrderHelper::create($request, $formulir);

        timeline_publish('create.point.finance.payment.order', 'create payment order "'. $formulir->form_number .'" success');
        DB::commit();

        gritter_success('create payment order "'. $formulir->form_number .'" success');
        return redirect('finance/point/payment-order/'.$payment_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.finance.payment.order');

        $view = view('point-finance::app.finance.point.payment-order.show');
        $view->payment_order = PaymentOrder::find($id);
        $view->cash_advance = $view->payment_order->cashAdvance;
        $view->list_payment_order_archived = PaymentOrder::joinFormulir()->archived($view->payment_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_payment_order_archived->count();
        return $view;
    }

    /**
     * Display archived resource
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function archived($id)
    {
        $view = view('point-finance::app.finance.point.payment-order.archived');
        $view->payment_order_archived = PaymentOrder::find($id);
        $view->payment_order = PaymentOrder::joinFormulir()->notArchived($view->payment_order_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Cancel a form
     *
     * @return array
     */
    public function cancel()
    {
        $permission_slug = app('request')->input('permission_slug');
        $formulir_id = app('request')->input('formulir_id');

        DB::beginTransaction();

        FormulirHelper::cancel($permission_slug, $formulir_id);
        PaymentHelper::cancelPaymentReference($formulir_id);

        DB::commit();

        return array('status' => 'success');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.point.finance.payment.order');

        $view = view('point-finance::app.finance.point.payment-order.edit');
        $view->payment_order = PaymentOrder::find($id);
        $view->list_coa = Coa::getNonSubledger();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->coa_expense = CoaPosition::where("name", "Expense")->first();
        $view->list_coa_category_expense = CoaCategory::where('coa_position_id', $view->coa_expense->id)->get();
        return $view;
    }

    /**
     * Display review edited form
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editReview($id)
    {
        access_is_allowed('update.point.finance.payment.order');

        $view = view('point-finance::app.finance.point.payment-order.edit-review');
        $view->payment_order = PaymentOrder::find($id);
        $view->payment_date = date_format_db(app('request')->input('payment_date'), app('request')->input('time'));
        $view->approval_to = User::find(app('request')->input('approval_to'));
        $view->person = Person::find(app('request')->input('person_id'));
        $view->payment_type = app('request')->input('payment_type');
        $view->coa_id = app('request')->input('coa_id');
        $view->notes = app('request')->input('notes');
        $view->amount = number_format_db(app('request')->input('amount'));
        $view->allocation_id = app('request')->input('allocation_id');
        $view->detail_notes = app('request')->input('detail_notes');
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
            'payment_date' => 'required',
            'approval_to' => 'required',
            'person_id' => 'required',
            'payment_type' => 'required',
            'coa_id' => 'required',
            'coa_allocation_id' => 'required',
            'other_notes' => 'required',
            'coa_value' => 'required',
        ]);

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));
        formulir_is_allowed_to_update('update.point.finance.payment.order', $request['form_date'], PaymentOrder::find($id)->formulir);

        DB::beginTransaction();
        $payment_order = PaymentOrder::find($id);
        $formulir_old = FormulirHelper::archive($request->input(), $payment_order->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $payment_order = PaymentOrderHelper::create($request, $formulir);
        PaymentHelper::cancelPaymentReference($formulir_old->id);
        timeline_publish('update.point.finance.payment.order', 'update payment order "'. $formulir->form_number .'" success');

        DB::commit();

        gritter_success('update payment order "'. $formulir->form_number .'" success');
        return redirect('finance/point/payment-order/'.$payment_order->id);
    }

    public function _createCoaExpense(Request $request)
    {
        access_is_allowed('create.coa');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
            'coa_category'=>'required',
        ]);
        
        $response = array('status' =>'failed');
        if ($validator->fails()) {
            return response()->json($response);
        }

        $coa = new Coa;
        $coa->name = $_POST['name'];
        $coa->coa_category_id = $_POST['coa_category'];
        $coa->created_by = auth()->user()->id;
        $coa->updated_by = auth()->user()->id;
        
        $check = Coa::where('name', $_POST['name'])->first();

        if (count($check) < 1) {
            $coa->save();
            $response = array(
                'status' => 'success',
                'code'=> $coa->id,
                'name'=> $coa->name,
             );
        }
        return response()->json($response);
    }
}

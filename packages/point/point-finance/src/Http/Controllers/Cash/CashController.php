<?php

namespace Point\PointFinance\Http\Controllers\Cash;

use Illuminate\Auth\id;
use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Helpers\PaymentHelper;
use Point\PointFinance\Models\Cash\Cash;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointFinance\Models\PaymentReference;

class CashController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.finance.cashier.cash');

        $view = view('point-finance::app.finance.point.cash.index');
        $view->list_cash = Cash::joinFormulir()->joinPerson()->notArchived()->selectOriginal();

        if (\Input::has('order_by')) {
            $view->list_cash = $view->list_cash->orderBy(\Input::get('order_by'), \Input::get('order_type'));
        } else {
            $view->list_cash = $view->list_cash->orderByStandard();
        }

        if (\Input::get('status') != 'all') {
            $view->list_cash = $view->list_cash->where('formulir.form_status', '=', \Input::get('status') ?: 0);
        }

        if (\Input::has('date_from')) {
            $view->list_cash = $view->list_cash->where('form_date', '>=', \DateHelper::formatDB(\Input::get('date_from'), 'start'));
        }

        if (\Input::has('date_to')) {
            $view->list_cash = $view->list_cash->where('form_date', '<=', \DateHelper::formatDB(\Input::get('date_to'), 'end'));
        }

        if (\Input::has('search')) {
            $view->list_cash = $view->list_cash->where(function ($q) {
                $q->where('formulir.notes', 'like', '%'.\Input::get('search').'%')
                    ->orWhere('formulir.form_number', 'like', '%'.\Input::get('search').'%')
                    ->orWhere('person.name', 'like', '%'.\Input::get('search').'%');
            });
        }

        if ((request()->get('database_name') == 'p_test' || request()->get('database_name') == 'p_personalfinance') && auth()->user()->name != 'lioni') {
            $view->list_cash = $view->list_cash->join('point_finance_cash_detail', 'point_finance_cash.id', '=', 'point_finance_cash_detail.point_finance_cash_id')
            ->join('coa', 'coa.id', '=', 'point_finance_cash_detail.coa_id')
            ->where('coa.name', 'not like', '%lioni%')
            ->groupBy('point_finance_cash_detail.point_finance_cash_id');
        }

        $view->list_cash = $view->list_cash->paginate(100);

        return $view;
    }

    public function createStep1()
    {
        access_is_allowed('create.point.finance.cashier.cash');

        $view = view('point-finance::app.finance.point.cash.create-step-1');
        $view->list_person = Person::active()->get();
        return $view;
    }

    public function createStep2In($person_id)
    {
        access_is_allowed('create.point.finance.cashier.cash');

        $view = view('point-finance::app.finance.point.cash.create-step-2');
        $view->person = Person::find($person_id);
        $view->pay_to = $person_id;
        $view->flag = 'in';
        $view->list_payment_order = PaymentHelper::getPaymentOrder($person_id);
        $view->list_coa = Coa::where('coa_category_id', 1)->where('disabled', 0)->get();

        return $view;
    }

    public function createStep2Out($payment_reference_id)
    {
        access_is_allowed('create.point.finance.cashier.cash');

        $payment_reference = PaymentReference::find($payment_reference_id);

        $view = view('point-finance::app.finance.point.cash.create-step-2-out');
        $view->person = Person::find($payment_reference->person_id);
        $view->payment_reference = $payment_reference;
        $view->pay_to = $payment_reference->person_id;
        $view->list_coa = Coa::where('coa_category_id', 1)->where('disabled', 0)->get();

        return $view;
    }

    public function createStep3(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'payment_date' => 'required',
            'coa_id' => 'required',
            'total_paid'=>'required|min:1',
            'notes' =>'required',
        ]);


        if ($validator->fails()) {
            return redirect('finance/point/cash/create-step-2-'.$request->input('flag').'/'.$request->input('pay_to'))
                        ->withErrors($validator)
                        ->withInput();
        }

        access_is_allowed('create.point.finance.cashier.cash');

        $view = view('point-finance::app.finance.point.cash.create-step-3');
        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->person = Person::find(\Input::get('person_id'));
        $view->flag = \Input::get('flag');
        $view->coa = Coa::find(\Input::get('coa_id'));
        $view->notes = \Input::get('notes');
        $view->list_payment = Formulir::whereIn('id', \Input::get('form_id'))->get();
        
        return $view;
    }

    public function editReview(Request $request)
    {
        access_is_allowed('create.point.finance.cashier.cash');

        $view = view('point-finance::app.finance.point.cash.edit-review');
        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->person = Person::find(\Input::get('person_id'));
        $view->flag = \Input::get('flag');
        $view->notes = \Input::get('notes');
        $view->id = \Input::get('id');


        $view->list_payment = Formulir::whereIn('id', \Input::get('form_id'))->get();
        $view->list_coa = Coa::find(\Input::get('coa_id'));
        $view->list_notes = \Input::get('detail_notes');
        $view->list_amount = number_format_db(\Input::get('paid'));
        $view->total = number_format_db(\Input::get('total_paid'));

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
        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));
        formulir_is_allowed_to_create('create.point.finance.cashier.cash', date_format_db($request->input('form_date'), $request->input('time')), $request->input('formulir_id'));

        \DB::beginTransaction();
        $formulir = FormulirHelper::create($request->input(), 'point-finance-cash-payment');
        $cash = PaymentHelper::insert($formulir, $request, 0);
        timeline_publish('create.cash.payment', 'successfully create'  . $formulir->form_number);
        \DB::commit();

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
        $view = view('point-finance::app.finance.point.cash.show');
        $view->cash = Cash::find($id);

        if ($view->cash->formulir->form_number == null) {
            return redirect('finance/point/cash/'.$view->cash->id.'/archived');
        }

        $view->list_cash_archived = Cash::joinFormulir()->archived($view->cash->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cash_archived->count();

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
        $view = view('point-finance::app.finance.point.cash.edit');
        $view->cash = Cash::find($id);
        if ($view->cash->total_payment < 0) {
            $view->flag = 'out';
        }
        $view->list_payment_order = PaymentHelper::getPaymentOrder($view->cash->person_id);
        $view->list_coa = Coa::where('coa_category_id', 1)->where('disabled', 0)->get();

        return $view;
    }

    public function archived($id)
    {
        $view = view('point-finance::app.finance.point.cash.archived');
        $view->cash = Cash::find($id);
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
        \DB::beginTransaction();

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));
        $cash = Cash::find($id);
        formulir_is_allowed_to_update('update.point.finance.cashier.cash', date_format_db($request->input('form_date'), $request->input('time')), $cash->formulir);
        foreach ($cash->detail as $old_detail) {
            $open = Formulir::find($old_detail->formulir_id);
            $open->form_status = 0;
            $open->save();
        }

        $formulir_old = FormulirHelper::archive($request->input(), $cash->formulir_id);
        JournalHelper::remove($formulir_old->id);
        AssetsRefer::where('payment_id', $formulir_old->id)->delete();
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $cash = PaymentHelper::insert($formulir, $request, 0);
        timeline_publish('update.cash.payment', 'payment '  . $formulir->form_number .' updated');

        \DB::commit();

        gritter_success('update cash payment'.$formulir->form_number .' success', 'false');
        return redirect('finance/point/cash');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printCash(Request $request, $id)
    {
        $view = view('point-finance::app.finance.point.cash.print');
        $view->cash = Cash::find($id);
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $view->warehouse_profiles = Warehouse::find($warehouse_id);
        } else {
            $view->warehouse_profiles = Warehouse::first();
        }
        if (!$view->warehouse_profiles) {
            throw new PointException('Please create your warehouse first to set your default name, address and phone number');
        }
        $view->project_name = $request->get('project')->name;
        return $view;
    }
}

<?php

namespace Point\Ksp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use App\Http\Controllers\Controller;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Coa;
use Point\Ksp\Models\LoanApplication;

class LoanApplicationController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.ksp.loan.application');

        $view = view('ksp::app.facility.ksp.loan-application.index');
        $view->list_loan_application = LoanApplication::joinFormulir()->notArchived()->selectOriginal()->orderByStandard();
        $view->list_loan_application = $view->list_loan_application->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.ksp.loan.application');

        $view = view('ksp::app.facility.ksp.loan-application.create');
        $view->list_customer = PersonHelper::getByType(['customer']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_account_bank = Coa::join('coa_category', 'coa_category_id', '=', 'coa_category.id')->where('coa_category.name', 'Bank Account')->get();
        $view->list_account_cash = Coa::join('coa_category', 'coa_category_id', '=', 'coa_category.id')->where('coa_category.name', 'Cash Account')->get();
        $view->payment_account;
        $view->loan_amount = 0;
        $view->periods = 12;
        $view->interest_rate = 0;
        $view->interest_rate_type = 'flat';
        $view->selected_customer;
        $view->payment_type;

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
            'customer_id' => 'required',
            'form_date' => 'required',
            'periods' => 'required',
            'loan_amount' => 'required',
            'interest_rate' => 'required',
            'interest_rate_type' => 'required',
            'payment_type' => 'required',
        ]);

        if ($request->get('submit_type') == 'calculate') {
            $view = view('ksp::app.facility.ksp.loan-application.create');
            $view->list_user_approval = UserHelper::getAllUser();
            $view->list_account_bank = Coa::join('coa_category', 'coa_category_id', '=', 'coa_category.id')->where('coa_category.name', 'Bank Account')->select('coa.*')->get();
            $view->list_account_cash = Coa::join('coa_category', 'coa_category_id', '=', 'coa_category.id')->where('coa_category.name', 'Cash Account')->select('coa.*')->get();
            $view->loan_amount = number_format_db($request->get('loan_amount'));
            $view->periods = number_format_db($request->get('periods'));
            $view->interest_rate = number_format_db($request->get('interest_rate'));
            $view->interest_rate_type = $request->get('interest_rate_type');
            $view->list_customer = PersonHelper::getByType(['customer']);
            $view->selected_customer = $request->get('customer_id');
            $view->payment_type = $request->get('payment_type');
            $view->payment_account = $request->get('payment_account');

            return $view;
        }

        if ($request->get('submit_type') == 'submit') {
            FormulirHelper::isAllowedToCreate('create.ksp.loan.application', date_format_db(app('request')->input('form_date')), []);

            DB::beginTransaction();

            $formulir = FormulirHelper::create($request->input(), 'ksp-loan-application');

            $loan_application = new LoanApplication;
            $loan_application->formulir_id = $formulir->id;
            $loan_application->customer_id = app('request')->input('customer_id');
            $loan_application->loan_amount = number_format_db(app('request')->input('loan_amount'));
            $loan_application->periods = number_format_db(app('request')->input('periods'));
            $loan_application->interest_rate = number_format_db(app('request')->input('interest_rate'));
            $loan_application->interest_rate_type = app('request')->input('interest_rate_type');
            $loan_application->payment_account_id = app('request')->input('payment_account');
            $loan_application->customer_id = $request->get('customer_id');
            $loan_application->payment_type = $request->get('payment_type');
            $loan_application->save();

            timeline_publish('create.ksp.loan.application', 'create loan application "' . $loan_application->formulir->form_number . '"');

            DB::commit();

            gritter_success('create loan application "'. $formulir->form_number .'" success');
            return redirect('facility/ksp/loan-application/' . $loan_application->id);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.ksp.loan.application');

        $view = view('ksp::app.facility.ksp.loan-application.show');
        $view->loan_application = LoanApplication::find($id);
        $view->list_loan_application_archived = LoanApplication::joinFormulir()->archived($view->loan_application->formulir->form_number)->selectOriginal()->orderByStandard()->get();
        $view->revision = $view->list_loan_application_archived->count();
        return $view;
    }

    public function archived($id)
    {
        // TODO:
//        access_is_allowed('read.ksp.loan.application');
//
//        $view = view('ksp::app.facility.ksp.loan-application.archived');
//        $view->buy_archived = LoanApplication::form($id)->first();
//        $view->shares_buy = LoanApplication::notArchived()->where('form_number', '=', $view->buy_archived->archived)->first();
//        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // TODO:
//        access_is_allowed('update.ksp.loan.application');
//
//        $view = view('ksp::app.facility.ksp.loan-application.edit');
//        $view->loan_application = LoanApplication::find($id);
//        $view->list_user_approval = UserHelper::getAllUser();
//        return $view;
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
        // TODO:
//        $this->validate($request, [
//        ]);
//
//        $loan_application = LoanApplication::find($id);
//        FormulirHelper::isAllowedToUpdate('update.bumi.shares.buy', date_format_db(app('request')->input('form_date'), app('request')->input('time')), $loan_application->formulir);
//
//        DB::beginTransaction();
//
//        $formulir_old = FormulirHelper::archive($request->input(), $loan_application->formulir_id);
//        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
//
//        $loan_application = new LoanApplication;
//        $loan_application->formulir_id = $formulir->id;
//        $loan_application->broker_id = app('request')->input('broker_id');
//        $loan_application->shares_id = app('request')->input('shares_id');
//        $loan_application->owner_id = app('request')->input('owner_id');
//        $loan_application->owner_group_id = app('request')->input('owner_group_id');
//        $loan_application->quantity = number_format_db(app('request')->input('quantity'));
//        $loan_application->price = number_format_db(app('request')->input('price'));
//        $loan_application->fee = number_format_db(app('request')->input('buy_fee'));
//        $loan_application->save();
//        SharesStockHelper::clear($formulir->id);
//
//        timeline_publish('update.bumi.shares.buy', 'update buy shares "' . $loan_application->shares->name . '" number ."' . $loan_application->formulir->form_number . '"');
//
//        DB::commit();
//
//        gritter_success('update loan application "'. $formulir->form_number .'" success');
//        return redirect('facility/ksp/loan-application/'.$loan_application->id);
    }

    public function cancel()
    {
        // TODO:
    }
}

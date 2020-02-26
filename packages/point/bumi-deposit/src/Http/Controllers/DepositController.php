<?php

namespace Point\BumiDeposit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\BumiDeposit\Models\DepositOwner;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\BumiDeposit\Models\Bank;
use Point\BumiDeposit\Helpers\DepositHelper;
use Point\BumiDeposit\Models\Deposit;
use Point\BumiDeposit\Models\DepositCategory;
use Point\BumiDeposit\Models\DepositGroup;

class DepositController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.deposit');

        $deposits = Deposit::joinFormulir()->joinDependencies()->selectOriginal()->notArchived()->active();


        if (auth()->user()->id > 3) {
            $deposits = $deposits->where(function ($q) {
                $q->where('bumi_deposit_group.name', 'BI')
                    ->orWhere('bumi_deposit_group.name', 'AM')
                    ->orWhere('bumi_deposit_group.name', 'BNS')
                    ->orWhere('bumi_deposit_group.name', 'BIJ')
                    ->orWhere('bumi_deposit_group.name', 'BIM')
                    ->orWhere('bumi_deposit_group.name', 'P-B')
                    ->orWhere('bumi_deposit_group.name', 'K-BI')
                    ->orWhere('bumi_deposit_group.name', 'R-BI')
                    ->orWhere('bumi_deposit_group.name', 'T-BI')
                    ->orWhere('bumi_deposit_group.name', 'T -BI')
                    ->orWhere('bumi_deposit_group.name', 'P- BI')
                    ->orWhere('bumi_deposit_group.name', 'D-BI')
                    ->orWhere('bumi_deposit_group.name', 'K-BI')
                    ->orWhere('bumi_deposit_group.name', 'R-BI')
                    ->orWhere('bumi_deposit_group.name', 't-BI')
                    ->orWhere('bumi_deposit_group.name', 'P');
            });
        }

        $deposits = DepositHelper::searchList($deposits,
            \Input::get('form_date_from'),
            \Input::get('form_date_to'),
            \Input::get('due_date_from'),
            \Input::get('due_date_to'),
            \Input::get('search'),
            \Input::get('select_field'));

        return view('bumi-deposit::app.facility.bumi-deposit.deposit.index', array(
            'deposits' => $deposits->paginate(100)
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.deposit');

        $view = view("bumi-deposit::app.facility.bumi-deposit.deposit.create", array(
            'banks' => Bank::active()->get(),
            'categories' => DepositCategory::active()->get(),
            'groups' => DepositGroup::active()->get(),
            'owners' => DepositOwner::active()->get()
        ));
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
            'deposit_bank_id' => 'required',
            'deposit_bank_account_id' => 'required',
            'deposit_bank_product_id' => 'required',
            'deposit_category_id' => 'required',
            'deposit_group_id' => 'required',
            'deposit_owner_id' => 'required',
            'deposit_time' => 'required',
            'original_amount' => 'required',
            'interest_percent' => 'required',
            'tax_percent' => 'required',
            'total_days_in_year' => 'required'
        ]);

        FormulirHelper::isAllowedToCreate('create.bumi.deposit', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'bumi-deposit');
        $deposit = DepositHelper::create($request, $formulir);
        timeline_publish('create.bumi.deposit', 'create deposit "'  . $deposit->formulir->form_number . '"');

        DB::commit();

        gritter_success('create deposit success');
        return redirect('facility/bumi-deposit/deposit/'.$deposit->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.bumi.deposit');

        $deposit = Deposit::find($id);

        $list_deposit_archived = Deposit::joinFormulir()
            ->joinBank()
            ->archived($deposit->formulir->form_number)
            ->selectOriginal()
            ->orderByDueDate();

        return view('bumi-deposit::app.facility.bumi-deposit.deposit.show', array(
            'deposit' => $deposit,
            'list_deposit_archived' => $list_deposit_archived->get(),
            'revision' => $list_deposit_archived->count()
        ));

        return redirect('facility/bumi-deposit');
    }

    public function archived($id)
    {
        access_is_allowed('read.bumi.deposit');
        $view = view('bumi-deposit::app.facility.bumi-deposit.deposit.archived');
        $view->deposit_archived = Deposit::find($id);
        $view->deposit = Deposit::joinFormulir()->notArchived($view->deposit_archived->formulir->archived)->selectOriginal()->first();
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
        access_is_allowed('update.bumi.deposit');

        return view('bumi-deposit::app.facility.bumi-deposit.deposit.edit', array(
            'banks'=> Bank::active()->get(),
            'categories'=> DepositCategory::active()->get(),
            'groups'=> DepositGroup::active()->get(),
            'owners' => DepositOwner::active()->get(),
            'deposit'=> Deposit::find($id)
        ));
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
            'deposit_bank_id' => 'required',
            'deposit_bank_account_id' => 'required',
            'deposit_bank_product_id' => 'required',
            'deposit_category_id' => 'required',
            'deposit_group_id' => 'required',
            'deposit_owner_id' => 'required',
            'deposit_time' => 'required',
            'original_amount' => 'required',
            'interest_percent' => 'required',
            'tax_percent' => 'required',
            'total_days_in_year' => 'required'
        ]);

        $deposit_old = Deposit::find($id);

        FormulirHelper::isAllowedToUpdate('update.bumi.deposit', date_format_db($request->input('form_date')), $deposit_old->formulir);

        DB::beginTransaction();

        $formulir_old = FormulirHelper::archive($request->input(), $deposit_old->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $deposit = DepositHelper::create($request, $formulir);
        if (formulir_is_close($deposit_old->formulir_id)) {
            DepositHelper::withdraw($request, $deposit);
        }

        timeline_publish('update.bumi.deposit', 'update deposit "'  . $deposit->formulir->form_number  . '"');

        DB::commit();

        gritter_success('update deposit success');
        return redirect('facility/bumi-deposit/deposit/'.$deposit->id);
    }

    /**
     *  Deposit Withdrawal.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function withdraw($id)
    {
        access_is_allowed('create.bumi.deposit');

        return view('bumi-deposit::app.facility.bumi-deposit.deposit.withdraw', array(
            'deposit' => Deposit::find($id)
        ));
    }

    public function storeWithdraw(Request $request, $id)
    {
        $this->validate($request, [
            'withdraw_date' => 'required',
            'withdraw_amount' => 'required',
            'withdraw_notes' => 'required'
        ]);

        FormulirHelper::isAllowedToCreate('create.bumi.deposit', date_format_db($request->input('withdraw_date')), []);

        DB::beginTransaction();

        $deposit = DepositHelper::withdraw($request, Deposit::find($id));
        timeline_publish('withdraw.bumi.deposit', 'withdraw deposit "'  . $deposit->formulir->form_number  .'"');

        DB::commit();

        gritter_success('withdraw deposit success');
        return redirect('facility/bumi-deposit/deposit/'.$deposit->id);
    }

    /**
     * Deposit Extend.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function extend($id)
    {
        return view('bumi-deposit::app.facility.bumi-deposit.deposit.extend', array(
            'deposit' => Deposit::find($id)
        ));
    }

    public function storeExtend(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'deposit_time' => 'required',
            'original_amount' => 'required',
            'interest_percent' => 'required',
            'tax_percent' => 'required',
            'total_days_in_year' => 'required'
        ]);

        FormulirHelper::isAllowedToCreate('create.bumi.deposit', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'bumi-deposit');
        $deposit = DepositHelper::extend($request, $formulir, Deposit::find($id));
        timeline_publish('extend.bumi.deposit', 'extend deposit "'  . $deposit->formulir->form_number . '"');

        DB::commit();

        gritter_success('extend deposit success');
        return redirect('facility/bumi-deposit/deposit/'.$deposit->id);
    }
}

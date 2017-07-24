<?php

namespace Point\BumiDeposit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Core\Models\Master\History;
use Point\Framework\Http\Controllers\Controller;
use Point\BumiDeposit\Models\Bank;
use Point\BumiDeposit\Models\BankAccount;
use Point\BumiDeposit\Models\BankProduct;

class BankController extends Controller
{
    use ValidationTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.deposit.bank');

        return view('bumi-deposit::app.facility.bumi-deposit.bank.index', array(
            'banks'=> Bank::search(\Input::get('search'))->orderBy('name')->paginate(100)
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.deposit.bank');

        return view('bumi-deposit::app.facility.bumi-deposit.bank.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        access_is_allowed('create.bumi.deposit.bank');

        $this->validate($request, [
            'name' => 'required',
            'branch' => 'required'
        ]);

        DB::beginTransaction();

        $bank = new Bank;
        $bank->name = $request->input('name');
        $bank->branch = $request->input('branch');
        $bank->address = $request->input('address');
        $bank->phone = $request->input('phone');
        $bank->fax = $request->input('fax');
        $bank->notes = $request->input('notes');
        $bank->save();

        $account_name = $request->input('account_name');
        $account_number = $request->input('account_number');
        $account_notes = $request->input('account_notes');

        for ($i=0 ; $i<count($account_name) ; $i++) {
            if (empty($account_name[$i])) {
                continue;
            }
            $bank_account = new BankAccount;
            $bank_account->bumi_deposit_bank_id = $bank->id;
            $bank_account->account_name = $account_name[$i];
            $bank_account->account_number = $account_number[$i];
            $bank_account->account_notes = $account_notes[$i];
            $bank_account->save();
        }

        $product_name = $request->input('product_name');
        $product_notes = $request->input('product_notes');

        for ($i=0 ; $i<count($product_name) ; $i++) {
            if (empty($product_name[$i])) {
                continue;
            }
            $bank_product = new BankProduct;
            $bank_product->bumi_deposit_bank_id = $bank->id;
            $bank_product->product_name = $product_name[$i];
            $bank_product->product_notes = $product_notes[$i];
            $bank_product->save();
        }

        timeline_publish('create.bumi.deposit.bank', 'create bank "'. $bank->name .'"');
        DB::commit();

        gritter_success('create bank success');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.bumi.deposit.bank');

        $bank = Bank::find($id);
        return view('bumi-deposit::app.facility.bumi-deposit.bank.show', array(
            'bank' => $bank,
            'histories' => History::show($bank->getTable(), $id)
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.bumi.deposit.bank');

        return view('bumi-deposit::app.facility.bumi-deposit.bank.edit', array(
            'bank' => Bank::find($id)
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
        access_is_allowed('update.bumi.deposit.bank');

        $this->validate($request, [
            'name' => 'required',
            'branch' => 'required'
        ]);

        DB::beginTransaction();

        $bank = Bank::find($id);
        $bank->name = $request->input('name');
        $bank->branch = $request->input('branch');
        $bank->address = $request->input('address');
        $bank->phone = $request->input('phone');
        $bank->fax = $request->input('fax');
        $bank->notes = $request->input('notes');
        $bank->save();

        $account_id_old = $request->input('account_id_old');
        $account_name_old = $request->input('account_name_old');
        $account_number_old = $request->input('account_number_old');
        $account_notes_old = $request->input('account_notes_old');

        foreach ($bank->accounts as $account) {
            if (! $account_id_old) {
                $bank_account = BankAccount::find($account->id);
                $bank_account->delete();
            } else {
                if (in_array($account->id, $account_id_old)) {
                    $index = array_search($account->id, $account_id_old);
                    $bank_account = BankAccount::find($account->id);
                    $bank_account->bumi_deposit_bank_id = $bank->id;
                    $bank_account->account_name = $account_name_old[$index];
                    $bank_account->account_number = $account_number_old[$index];
                    $bank_account->account_notes = $account_notes_old[$index];
                    $bank_account->save();
                } else {
                    $bank_account = BankAccount::find($account->id);
                    $bank_account->delete();
                }
            }
        }

        $product_id_old = $request->input('product_id_old');
        $product_name_old = $request->input('product_name_old');
        $product_notes_old = $request->input('product_notes_old');

        foreach ($bank->products as $product) {
            if (! $product_id_old) {
                $bank_product = BankProduct::find($product->id);
                $bank_product->delete();
            } else {
                if (in_array($product->id, $product_id_old)) {
                    $index = array_search($product->id, $product_id_old);
                    $bank_product = BankProduct::find($product->id);
                    $bank_product->bumi_deposit_bank_id = $bank->id;
                    $bank_product->product_name = $product_name_old[$index];
                    $bank_product->product_notes = $product_notes_old[$index];
                    $bank_product->save();
                } else {
                    $bank_product = BankProduct::find($product->id);
                    $bank_product->delete();
                }
            }
        }

        $account_name = $request->input('account_name');
        $account_number = $request->input('account_number');
        $account_notes = $request->input('account_notes');

        for ($i=0 ; $i<count($account_name) ; $i++) {
            if (empty($account_name[$i])) {
                continue;
            }
            $bank_account = new BankAccount;
            $bank_account->bumi_deposit_bank_id = $bank->id;
            $bank_account->account_name = $account_name[$i];
            $bank_account->account_number = $account_number[$i];
            $bank_account->account_notes = $account_notes[$i];
            $bank_account->save();
        }

        $product_name = $request->input('product_name');
        $product_notes = $request->input('product_notes');

        for ($i=0 ; $i<count($product_name) ; $i++) {
            if (empty($product_name[$i])) {
                continue;
            }
            $bank_product = new BankProduct;
            $bank_product->bumi_deposit_bank_id = $bank->id;
            $bank_product->product_name = $product_name[$i];
            $bank_product->product_notes = $product_notes[$i];
            $bank_product->save();
        }
        timeline_publish('update.bumi.deposit.bank', 'update bank "'. $bank->name .'"');
        DB::commit();

        gritter_success('update bank success');
        return redirect('facility/bumi-deposit/bank/'.$bank->id);
    }

    public function delete()
    {
        access_is_allowed('delete.bumi.deposit.bank');

        $redirect = false;

        if (\Input::get('redirect')) {
            $redirect = \Input::get('redirect');
        }

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(auth()->user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        try {
            \DB::beginTransaction();
            $bank = Bank::find(\Input::get('id'));
            $bank->delete();
            timeline_publish('delete.bumi.deposit.bank', 'delete bank "'. $bank->name .'"');
            \DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'delete bank success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('delete bank "'. $bank->bank_name .'" success');
        }

        return $response;
    }

    public function _select()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $bank = Bank::find(\Input::get('bank_id'));
        $bank_accounts = [];
        $bank_account_id = 0;
        $bank_products = [];
        $bank_product_id = 0;
        foreach ($bank->accounts as $account) {
            array_push($bank_accounts, [
                'text'=>$account->account_name . ' | ' . $account->account_number,
                'value'=>$account->id
            ]);
            $bank_account_id = $account->id;
        }

        foreach ($bank->products as $product) {
            array_push($bank_products, [
                'text'=>$product->product_name,
                'value'=>$product->id
            ]);
            $bank_product_id = $product->id;
        }

        $response = array(
            'list_account' => $bank_accounts,
            'list_product' => $bank_products,
            'bank_account_id' => $bank_account_id,
            'bank_product_id' => $bank_product_id
        );
        return response()->json($response);
    }
}

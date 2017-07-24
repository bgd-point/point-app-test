<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\BumiShares\Helpers\SharesStockHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\BumiShares\Models\Buy;
use Point\BumiShares\Models\Broker;
use Point\BumiShares\Models\Shares;
use Point\BumiShares\Models\Owner;
use Point\BumiShares\Models\OwnerGroup;
use Point\BumiShares\Traits\StockAdjustmentTrait;
use App\Http\Controllers\Controller;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;

class BuyController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.shares.buy');

        $view = view('bumi-shares::app.facility.bumi-shares.buy.index');
        $view->list_shares_buy = Buy::joinDependence()->notArchived()->selectOriginal();
        
        if (\Input::get('order_by')) {
            $view->list_shares_buy = $view->list_shares_buy->orderBy(\Input::get('order_by'), \Input::get('order_type'));
        } else {
            $view->list_shares_buy = $view->list_shares_buy->orderByStandard();
        }

        if (\Input::get('status') != 'all') {
            $view->list_shares_buy = $view->list_shares_buy->where('formulir.form_status', '=', \Input::get('status') ?: 0);
        }

        if (\Input::get('date_from')) {
            $view->list_shares_buy = $view->list_shares_buy->where('form_date', '>=', date_format_db(app('request')->input('date_from'), 'start'));
        }

        if (\Input::get('date_to')) {
            $view->list_shares_buy = $view->list_shares_buy->where('form_date', '<=', date_format_db(app('request')->input('date_to'), 'end'));
        }

        if (\Input::get('search')) {
            $view->list_shares_buy = $view->list_shares_buy->where(function ($q) {
                $q->where('bumi_shares.name', 'like', '%'.app('request')->input('search').'%')
                    ->orWhere('bumi_shares_broker.name', 'like', '%'.app('request')->input('search').'%')
                    ->orWhere('bumi_shares_owner.name', 'like', '%'.app('request')->input('search').'%')
                    ->orWhere('bumi_shares_owner_group.name', 'like', '%'.app('request')->input('search').'%');
            });
        }

        $view->list_shares_buy = $view->list_shares_buy->paginate(100);

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        access_is_allowed('create.bumi.shares.buy');

        $view = view('bumi-shares::app.facility.bumi-shares.buy.create-step-1');
        $view->list_owner_group = OwnerGroup::all();
        $view->list_owner = Owner::all();
        $view->list_broker = Broker::all();
        $view->list_shares = Shares::all();
        return $view;
    }

    public function createStep2(Request $request)
    {
        access_is_allowed('create.bumi.shares.buy');

        $this->validate($request, [
            'broker_id' => 'required',
            'shares_id' => 'required',
            'owner_id' => 'required',
            'owner_group_id' => 'required',
        ]);

        $view = view('bumi-shares::app.facility.bumi-shares.buy.create-step-2');
        $view->list_user_approval = UserHelper::getAllUser();
        $view->form_date = app('request')->input('form_date');
        $view->time = app('request')->input('time');
        $view->broker = Broker::find(app('request')->input('broker_id'));
        $view->buy_fee = $view->broker->buy_fee;
        $view->shares = Shares::find(app('request')->input('shares_id'));
        $view->owner = Owner::find(app('request')->input('owner_id'));
        $view->owner_group = OwnerGroup::find(app('request')->input('owner_group_id'));
        $view->notes = app('request')->input('notes');
        $view->quantity;
        $view->price;
        $view->total;
        $view->approval_to = 0;

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
        $validator = \Validator::make($request->all(), [
            'quantity' => 'required',
            'price' => 'required',
            'approval_to' => 'required',
        ]);

        if ($validator->fails()) {
            $view = view('bumi-shares::app.facility.bumi-shares.buy.create-step-2');
            $view->list_user_approval = UserHelper::getAllUser();
            $view->form_date = $request->form_date;
            $view->time = $request->time;
            $view->broker = Broker::find($request->broker_id);
            $view->buy_fee = $request->buy_fee;
            $view->shares = Shares::find($request->shares_id);
            $view->owner = Owner::find($request->owner_id);
            $view->owner_group = OwnerGroup::find($request->owner_group_id);
            $view->notes = $request->notes;
            $view->quantity = number_format_db($request->quantity);
            $view->price = number_format_db($request->price);
            $view->total = $request->total;

            return $view->withErrors($validator);
        }

        FormulirHelper::isAllowedToCreate('create.bumi.shares.buy', date_format_db(app('request')->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'bumi-shares-buy');

        $shares_buy = new Buy;
        $shares_buy->formulir_id = $formulir->id;
        $shares_buy->broker_id = app('request')->input('broker_id');
        $shares_buy->shares_id = app('request')->input('shares_id');
        $shares_buy->owner_id = app('request')->input('owner_id');
        $shares_buy->owner_group_id = app('request')->input('owner_group_id');
        $shares_buy->quantity = number_format_db(app('request')->input('quantity'));
        $shares_buy->price = number_format_db(app('request')->input('price'));
        $shares_buy->fee = number_format_db(app('request')->input('buy_fee'));
        $shares_buy->save();

        timeline_publish('create.bumi.shares.buy', 'buy shares "' . $shares_buy->shares->name . '"');

        DB::commit();

        gritter_success('buy shares "'. $formulir->form_number .'" success');
        return redirect('facility/bumi-shares/buy');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.bumi.shares.buy');

        $view = view('bumi-shares::app.facility.bumi-shares.buy.show');
        $shares_buy = Buy::find($id);
        $view->shares_buy = $shares_buy;
        $view->list_shares_buy_archived = Buy::joinFormulir()->archived($view->shares_buy->formulir->form_number)->get();
        $view->revision = $view->list_shares_buy_archived->count();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.bumi.shares.buy');

        $view = view('bumi-shares::app.facility.bumi-shares.buy.archived');
        $view->buy_archived = Buy::form($id)->first();
        $view->shares_buy = Buy::joinDependence()->notArchived()->where('form_number', '=', $view->buy_archived->archived)->first();
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
        access_is_allowed('update.bumi.shares.buy');

        $view = view('bumi-shares::app.facility.bumi-shares.buy.edit');
        $view->shares_buy = Buy::find($id);
        $view->list_owner_group = OwnerGroup::all();
        $view->list_owner = Owner::all();
        $view->list_broker = Broker::all();
        $view->list_shares = Shares::all();
        $view->list_user_approval = UserHelper::getAllUser();
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
            'broker_id' => 'required',
            'shares_id' => 'required',
            'owner_id' => 'required',
            'owner_group_id' => 'required',
            'quantity' => 'required',
            'price' => 'required',
            'approval_to' => 'required',
        ]);

        $shares_buy = Buy::find($id);
        FormulirHelper::isAllowedToUpdate('update.bumi.shares.buy', date_format_db(app('request')->input('form_date'), app('request')->input('time')), $shares_buy->formulir);

        DB::beginTransaction();
        
        $formulir_old = FormulirHelper::archive($request->input(), $shares_buy->formulir_id);
        SharesStockHelper::clear($formulir_old->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);

        $shares_buy = new Buy;
        $shares_buy->formulir_id = $formulir->id;
        $shares_buy->broker_id = app('request')->input('broker_id');
        $shares_buy->shares_id = app('request')->input('shares_id');
        $shares_buy->owner_id = app('request')->input('owner_id');
        $shares_buy->owner_group_id = app('request')->input('owner_group_id');
        $shares_buy->quantity = number_format_db(app('request')->input('quantity'));
        $shares_buy->price = number_format_db(app('request')->input('price'));
        $shares_buy->fee = number_format_db(app('request')->input('buy_fee'));
        $shares_buy->save();
        SharesStockHelper::clear($formulir->id);

        timeline_publish('update.bumi.shares.buy', 'update buy shares "' . $shares_buy->shares->name . '" number ."' . $shares_buy->formulir->form_number . '"');

        DB::commit();

        gritter_success('update buy shares "'. $formulir->form_number .'" success');
        return redirect('facility/bumi-shares/buy/'.$shares_buy->id);
    }

    public function cancel()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(\Auth::user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        $formulir_id = \Input::get('formulir_id');
        $permission_slug = \Input::get('permission_slug');
        DB::beginTransaction();

        try {
            $formulir = Formulir::find($formulir_id);

            FormulirHelper::isAllowedToCancel($permission_slug, $formulir);
            $formulir->form_status = -1;
            $formulir->canceled_at = date('Y-m-d H:i:s');
            $formulir->canceled_by = auth()->user()->id;
            $formulir->save();

            FormulirHelper::unlock($formulir->id);
            ReferHelper::cancel($formulir->formulirable_type, $formulir->formulirable_id);
            SharesStockHelper::clear($formulir->id);
        } catch (\Exception $e) {
            return response()->json(array(
                'status' => 'error',
                'title' => 'Cannot cancel this form',
                'msg' => 'This shares already sold'
            ));
        }

        DB::commit();

        timeline_publish('cancel form ', $formulir->form_number);

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Canceled Form Success'
        );

        return $response;
    }
}

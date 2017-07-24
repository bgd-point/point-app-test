<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\BumiShares\Helpers\SharesStockHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Helpers\FormulirHelper;
use Point\BumiShares\Models\Stock;
use Point\BumiShares\Models\Sell;
use Point\BumiShares\Models\Broker;
use Point\BumiShares\Models\Shares;
use Point\BumiShares\Models\Owner;
use Point\BumiShares\Models\OwnerGroup;
use App\Http\Controllers\Controller;

class SellController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.shares.sell');

        $view = view('bumi-shares::app.facility.bumi-shares.sell.index');
        $view->list_shares_sell = Sell::joinDependence()->notArchived()->selectOriginal();
        
        if (\Input::get('order_by')) {
            $view->list_shares_sell = $view->list_shares_sell->orderBy(\Input::get('order_by'), \Input::get('order_type'));
        } else {
            $view->list_shares_sell = $view->list_shares_sell->orderByStandard();
        }

        if (\Input::get('status') != 'all') {
            $view->list_shares_sell = $view->list_shares_sell->where('formulir.form_status', '=', \Input::get('status') ?: 0);
        }

        if (\Input::has('date_from')) {
            $view->list_shares_sell = $view->list_shares_sell->where('form_date', '>=', date_format_db(app('request')->input('date_from'), 'start'));
        }

        if (\Input::has('date_to')) {
            $view->list_shares_sell = $view->list_shares_sell->where('form_date', '<=', date_format_db(app('request')->input('date_to'), 'end'));
        }

        if (\Input::has('search')) {
            $view->list_shares_sell = $view->list_shares_sell->where(function ($q) {
                $q->where('bumi_shares.name', 'like', '%'.app('request')->input('search').'%')
                    ->orWhere('bumi_shares_broker.name', 'like', '%'.app('request')->input('search').'%')
                    ->orWhere('bumi_shares_owner.name', 'like', '%'.app('request')->input('search').'%')
                    ->orWhere('bumi_shares_owner_group.name', 'like', '%'.app('request')->input('search').'%');
            });
        }
        $view->list_shares_sell = $view->list_shares_sell->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        access_is_allowed('create.bumi.shares.sell');

        $view = view('bumi-shares::app.facility.bumi-shares.sell.create-step-1');
        $view->list_owner_group = OwnerGroup::all();
        $view->list_owner = Owner::all();
        $view->list_broker = Broker::all();
        $view->list_shares = Shares::all();
        return $view;
    }

    public function createStep2(Request $request)
    {
        $this->validate($request, [
            'broker_id' => 'required',
            'shares_id' => 'required',
            'owner_id' => 'required',
            'owner_group_id' => 'required',
        ]);

        $available_stock = Stock::availableStock(app('request')->input('shares_id'), app('request')->input('owner_group_id'),
           app('request')->input('broker_id'), app('request')->input('owner_id'));

        if (! $available_stock) {
            gritter_error('your query doesn\'t have available stock to sell');
            return redirect()->back()->withInput();
        }
        
        $view = view('bumi-shares::app.facility.bumi-shares.sell.create-step-2');
        $view->list_user_approval = UserHelper::getAllUser();
        $view->available_stock = $available_stock;
        $view->form_date = app('request')->input('form_date');
        $view->time = app('request')->input('time');
        $view->broker = Broker::find(app('request')->input('broker_id'));
        $view->shares = Shares::find(app('request')->input('shares_id'));
        $view->owner = Owner::find(app('request')->input('owner_id'));
        $view->owner_group = OwnerGroup::find(app('request')->input('owner_group_id'));
        $view->notes = app('request')->input('notes');
        $view->quantity = '';
        $view->price = '';
        $view->total = '';
        $view->approval_to = '';
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
        $max = number_format_db($request->available_stock);
        
        $validator = \Validator::make($request->all(), [
            'quantity' => "required|min:1|max:$max",
            'price' => 'required',
            'approval_to' => 'required',
        ]);

        if ($validator->fails()) {
            $view = view('bumi-shares::app.facility.bumi-shares.sell.create-step-2');
            $view->list_user_approval = UserHelper::getAllUser();
            $view->available_stock = $request->availablestock;
            $view->form_date = $request->form_date;
            $view->time = $request->time;
            $view->broker = Broker::find($request->broker_id);
            $view->shares = Shares::find($request->shares_id);
            $view->owner = Owner::find($request->owner_id);
            $view->owner_group = OwnerGroup::find($request->owner_group_id);
            $view->notes = $request->notes;

            $view->quantity = number_format_db($request->quantity);
            $view->price = number_format_db($request->price);
            $view->total = number_format_db($request->total);

            return $view->withErrors($validator);
        }

        FormulirHelper::isAllowedToCreate('create.bumi.shares.sell', date_format_db(app('request')->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'bumi-shares-sell');

        $shares_sell = new Sell;
        $shares_sell->formulir_id = $formulir->id;
        $shares_sell->broker_id = app('request')->input('broker_id');
        $shares_sell->shares_id = app('request')->input('shares_id');
        $shares_sell->owner_id = app('request')->input('owner_id');
        $shares_sell->owner_group_id = app('request')->input('owner_group_id');
        $shares_sell->quantity = number_format_db(app('request')->input('quantity'));
        $shares_sell->price = number_format_db(app('request')->input('price'));
        $shares_sell->fee = number_format_db(app('request')->input('sales_fee'));
        $shares_sell->save();

        $available_stock = SharesStockHelper::isQuantityAvailable($shares_sell);
        if (! $available_stock) {
            gritter_error('selling at this quantity is not available');
            return redirect('facility/bumi-shares/sell');
        }

        timeline_publish('create.bumi.shares.sell', 'sell shares '. $shares_sell->shares->name. ' number "'. $formulir->form_number .'"');

        DB::commit();

        gritter_success('sell shares "'. $formulir->form_number .'" success');

        return redirect('facility/bumi-shares/sell');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.bumi.shares.sell');

        $view = view('bumi-shares::app.facility.bumi-shares.sell.show');
        $shares_sell = Sell::find($id);
        $view->shares_sell = $shares_sell;
        $view->list_shares_sell_archived = Sell::archived()->where('archived', '=', $view->shares_sell->formulir->form_number)->get();
        $view->revision = $view->list_shares_sell_archived->count();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.bumi.shares.sell');

        $view = view('bumi-shares::app.facility.bumi-shares.sell.archived');
        $view->shares_sell_archived = Sell::form($id)->first();
        $view->shares_sell = Sell::notArchived()->where('form_number', '=', $view->shares_sell_archived->archived)->first();
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
        access_is_allowed('update.bumi.shares.sell');

        $view = view('bumi-shares::app.facility.bumi-shares.sell.edit');
        $view->shares_sell = Sell::find($id);
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

        FormulirHelper::isAllowedToUpdate('update.bumi.shares.sell', date_format_db(app('request')->input('form_date'), app('request')->input('time')), Sell::find($id)->formulir);

        DB::beginTransaction();

        $formulir_old = FormulirHelper::archive($request->input(), Sell::find($id)->formulir_id);
        SharesStockHelper::remove($formulir_old->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);

        $shares_sell = new Sell;
        $shares_sell->formulir_id = $formulir->id;
        $shares_sell->broker_id = app('request')->input('broker_id');
        $shares_sell->shares_id = app('request')->input('shares_id');
        $shares_sell->owner_id = app('request')->input('owner_id');
        $shares_sell->owner_group_id = app('request')->input('owner_group_id');
        $shares_sell->quantity = number_format_db(app('request')->input('quantity'));
        $shares_sell->price = number_format_db(app('request')->input('price'));
        $shares_sell->fee = number_format_db(app('request')->input('sales_fee'));
        $shares_sell->save();

        timeline_publish('update.bumi.shares.sell', 'update sell shares '. $shares_sell->shares->name. ' number "'. $formulir->form_number .'"');

        DB::commit();

        gritter_success('update sell shares "'. $formulir->form_number .'" success');
        return redirect('facility/bumi-shares/sell/'.$shares_sell->id);
    }

    /**
     * Cancel form
     * @return array|\Illuminate\Http\JsonResponse
     */
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
            SharesStockHelper::remove($formulir->id);
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
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

<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;

use Point\BumiShares\Models\Broker;

class BrokerController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.shares.broker');

        $view = view('bumi-shares::app.facility.bumi-shares.broker.index');
        $view->list_broker = Broker::search(app('request')->input('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.shares.broker');

        return view('bumi-shares::app.facility.bumi-shares.broker.create');
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
            'name' => 'required|unique:bumi_shares_broker',
            'buy_fee' => 'required',
            'sales_fee' => 'required'
        ]);

        DB::beginTransaction();
        $broker = new Broker;
        $broker->name = app('request')->input('name');
        $broker->notes = app('request')->input('notes');
        $broker->buy_fee = number_format_db(app('request')->input('buy_fee'));
        $broker->sales_fee = number_format_db(app('request')->input('sales_fee'));
        $broker->created_by = auth()->user()->id;
        $broker->updated_by = auth()->user()->id;
        $broker->save();
        timeline_publish('create.bumi.shares.broker', 'create broker "'. $broker->name .'"');
        DB::commit();

        gritter_success('add broker "'. $broker->name .'" success');

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
        access_is_allowed('read.bumi.shares.broker');

        $view = view('bumi-shares::app.facility.bumi-shares.broker.show');
        $view->broker = Broker::find($id);
        $view->histories = History::show('bumi_shares_broker', $id);
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
        access_is_allowed('update.bumi.shares.broker');

        $view = view('bumi-shares::app.facility.bumi-shares.broker.edit');
        $view->broker = Broker::find($id);
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
            'name' => 'required|unique:bumi_shares_broker,name,' . $id,
            'buy_fee' => 'required',
            'sales_fee' => 'required'
        ]);

        DB::beginTransaction();
        $broker = Broker::find($id);
        $broker->notes = app('request')->input('notes');
        $broker->name = app('request')->input('name');
        $broker->sales_fee = number_format_db(app('request')->input('sales_fee'));
        $broker->buy_fee = number_format_db(app('request')->input('buy_fee'));
        $broker->updated_by = auth()->user()->id;
        $broker->save();
        timeline_publish('update.bumi.shares.broker', 'update broker "'. $broker->name .'"');

        DB::commit();

        gritter_success('update broker "'. $broker->name .'" success');
        return redirect('facility/bumi-shares/broker/'.$id);
    }

    public function delete()
    {
        $redirect = false;

        if (app('request')->input('redirect')) {
            $redirect = app('request')->input('redirect');
        }

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(auth()->user()->name, app('request')->input('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        try {
            $broker = Broker::find(app('request')->input('id'));
            $broker->delete();
            timeline_publish('delete.bumi.shares.broker', 'delete broker "'. $broker->name .'"');
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Delete Success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('delete broker "'. $broker->name .'" success');
        }

        return $response;
    }
}

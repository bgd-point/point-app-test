<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\WarehouseHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\Core\Models\User;

class WarehouseController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.warehouse');

        $view = view('framework::app.master.warehouse.index');
        $view->list_warehouse = Warehouse::search(\Input::get('status'), \Input::get('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.warehouse');

        $view = view('framework::app.master.warehouse.create');
        $view->code = WarehouseHelper::getLastCode();
        $view->list_petty_cash_account = Coa::joinCategory()->where('coa_category.name', '=', 'Petty Cash')->selectOriginal()->get();
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        access_is_allowed('create.warehouse');

        DB::beginTransaction();
        $this->validate($request, [
            'code' => 'required|unique:warehouse',
            'name' => 'required|unique:warehouse',
            'petty_cash_account' => 'required'
        ]);
        $warehouse = new Warehouse;
        $warehouse->petty_cash_account = $request->input('petty_cash_account');
        $warehouse->code = $request->input('code');
        $warehouse->name = $request->input('name');
        $warehouse->store_name = $request->input('store_name');
        $warehouse->address = $request->input('address');
        $warehouse->phone = $request->input('phone');
        $warehouse->created_by = auth()->user()->id;
        $warehouse->updated_by = auth()->user()->id;

        if (!$warehouse->save()) {
            gritter_error(trans('framework::framework/master.warehouse.create.failed', ['name' => $warehouse->name]));
            return redirect()->back();
        }

        timeline_publish('create.warehouse', trans('framework::framework/master.warehouse.create.timeline', ['name' => $warehouse->name]));
        DB::commit();

        gritter_success(trans('framework::framework/master.warehouse.create.success', ['name' => $warehouse->name]));
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.warehouse');

        $view = view('framework::app.master.warehouse.show');
        $view->warehouse = Warehouse::find($id);
        $view->histories = History::show('warehouse', $id);
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.warehouse');

        $view = view('framework::app.master.warehouse.edit');
        $view->list_petty_cash_account = Coa::joinCategory()->where('coa_category.name', '=', 'Petty Cash')->selectOriginal()->get();
        $view->warehouse = Warehouse::find($id);
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        access_is_allowed('update.warehouse');

        DB::beginTransaction();

        $this->validate($request, [
            'code' => 'required|unique:warehouse,code,' . $id,
            'name' => 'required|unique:warehouse,name,' . $id,
            'petty_cash_account' => 'required'
        ]);

        $warehouse = Warehouse::find($id);
        $warehouse->petty_cash_account = $request->input('petty_cash_account');
        $warehouse->code = $request->input('code');
        $warehouse->name = $request->input('name');
        $warehouse->store_name = $request->input('store_name');
        $warehouse->address = $request->input('address');
        $warehouse->phone = $request->input('phone');
        $warehouse->updated_by = auth()->user()->id;

        if (!$warehouse->save()) {
            gritter_error(trans('framework::framework/master.warehouse.create.failed', ['name' => $warehouse->name]));
            return redirect()->back();
        }

        timeline_publish('update.warehouse', trans('framework::framework/master.warehouse.update.timeline', ['name' => $warehouse->name]));
        DB::commit();

        gritter_success(trans('framework::framework/master.warehouse.update.success', ['name' => $warehouse->name]));
        return redirect('master/warehouse/' . $id);
    }

    public function setUser()
    {
        access_is_allowed('create.user');

        $view = view('framework::app.master.warehouse.set_user');
        $list_warehouse = Warehouse::active()->get();
        $list_user = User::get();
        foreach ($list_user as $user) {
            $user->warehouse;
        }
        return $view->with('list_user', $list_user)->with('list_warehouse', $list_warehouse);
    }

    public function updateUserWarehouse()
    {
        access_is_allowed('create.user');

        $warehouse_id = app('request')->input('warehouse_id');
        $user_id = app('request')->input('user_id');
        $value = app('request')->input('value');

        if ($value === true) {
            $user_warehouse = new UserWarehouse;
            $user_warehouse->user_id = $user_id;
            $user_warehouse->warehouse_id = $warehouse_id;
            $user_warehouse->save();
        }
        else {
            $user_warehouse = UserWarehouse::where('user_id', $user_id)
                                           ->where('warehouse_id', $warehouse_id)
                                           ->delete();
        }
    }

    public function delete()
    {
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
            DB::beginTransaction();
            $warehouse = Warehouse::find(\Input::get('id'));
            $warehouse->delete();

            timeline_publish('delete.warehouse', trans('framework::framework/master.warehouse.delete.timeline', ['name' => $warehouse->name]));

            DB::commit();
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
            gritter_success(trans('framework::framework/master.warehouse.delete.success', ['name' => $warehouse->name]));
        }

        return $response;
    }

    public function _list()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $warehouses = Warehouse::all();
        $array_warehouse = [];
        foreach ($warehouses as $warehouse) {
            array_push($array_warehouse, ['text' => $warehouse->name, 'value' => $warehouse->id]);
        }

        $response = array(
            'lists' => $array_warehouse
        );
        return response()->json($response);
    }

    public function _insert(Request $request)
    {
        access_is_allowed('create.warehouse');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'petty_cash_account' => 'required',
        ]);

        $response = array('status' => 'failed');

        if ($validator->fails()) {
            return response()->json($response);
        }

        $check = Warehouse::where('name', $request->input('name'))->first();
        if (count($check) > 0) {
            return response()->json($response);
        }

        $warehouse = new Warehouse;
        $warehouse->petty_cash_account = $request->input('petty_cash_account');
        $warehouse->code = WarehouseHelper::getLastCode();
        $warehouse->name = $request->input('name');
        $warehouse->created_by = auth()->user()->id;
        $warehouse->updated_by = auth()->user()->id;

        $warehouse->save();

        $response = array(
            'status' => 'success',
            'id' => $warehouse->id,
            'name' => $warehouse->name,
        );

        return response()->json($response);
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.warehouse')) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $warehouse = Warehouse::find($request->input('index'));

        if (!$warehouse) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $warehouse->disabled = $warehouse->disabled == 0 ? 1 : 0;
        $warehouse->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $warehouse->disabled);

        return response()->json($response);
    }
}

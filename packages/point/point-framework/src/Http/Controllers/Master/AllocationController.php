<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Allocation;

class AllocationController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->may('read.allocation')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.allocation.index');
        $view->list_allocation = Allocation::search(\Input::get('status'), \Input::get('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->may('create.allocation')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.allocation.create');
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
        if (!auth()->user()->may('create.allocation')) {
            return view('core::errors.restricted');
        }

        DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:allocation'
        ]);
        $allocation = new Allocation;
        $allocation->name = $request->input('name');
        $allocation->created_by = auth()->user()->id;
        $allocation->updated_by = auth()->user()->id;

        if (!$allocation->save()) {
            gritter_error(trans('framework::framework/master.allocation.create.failed', ['name' => $allocation->name]));
            return redirect()->back();
        }

        timeline_publish('create.allocation', trans('framework::framework/master.allocation.create.timeline', ['name' => $allocation->name]));
        DB::commit();

        gritter_success(trans('framework::framework/master.allocation.create.success', ['name' => $allocation->name]));
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
        if (!auth()->user()->may('read.allocation')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.allocation.show');
        $view->allocation = Allocation::find($id);
        $view->histories = History::show('allocation', $id);
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
        if (!auth()->user()->may('update.allocation')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.allocation.edit');
        $view->allocation = Allocation::find($id);
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
        if (!auth()->user()->may('update.allocation')) {
            return view('core::errors.restricted');
        }

        DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:allocation,name,' . $id
        ]);
        $allocation = Allocation::find($id);
        $allocation->name = \Input::get('name');
        $allocation->updated_by = auth()->user()->id;

        if (!$allocation->save()) {
            gritter_error(trans('framework::framework/master.allocation.update.failed', ['name' => $allocation->name]));
            return redirect()->back();
        }

        timeline_publish('update.allocation', trans('framework::framework/master.allocation.update.timeline', ['name' => $allocation->name]));
        DB::commit();

        gritter_success(trans('framework::framework/master.allocation.update.success', ['name' => $allocation->name]));
        return redirect('master/allocation/' . $id);
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
            $allocation = Allocation::find(\Input::get('id'));
            $allocation->delete();

            timeline_publish('delete.allocation', trans('framework::framework/master.allocation.delete.timeline', ['name' => $allocation->name]));

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
            gritter_error(trans('framework::framework/master.allocation.delete.failed', ['name' => $allocation->name]));
        }

        return $response;
    }

    public function _create(Request $request)
    {
        access_is_allowed('create.allocation');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        $response = array('status' => 'failed');
        if ($validator->fails()) {
            return response()->json($response);
        }

        $allocation = new Allocation;
        $allocation->name = $_POST['name'];

        $check = Allocation::where('name', $_POST['name'])->first();

        if (count($check) < 1) {
            $allocation->save();
            $response = array(
                'status' => 'success',
                'code' => $allocation->id,
                'name' => $allocation->name,
            );
        }
        return response()->json($response);
    }

    public function _list()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $allocations = Allocation::all();
        $list_allocation = [];
        foreach ($allocations as $allocation) {
            array_push($list_allocation, ['text' => $allocation->name, 'value' => $allocation->id]);
        }

        $response = array(
            'lists' => $list_allocation,
        );
        return response()->json($response);
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.allocation')) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $allocation = Allocation::find($request->input('index'));

        if (!$allocation) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $allocation->disabled = $allocation->disabled == 0 ? 1 : 0;
        $allocation->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $allocation->disabled);

        return response()->json($response);
    }
}

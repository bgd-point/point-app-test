<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Unit;

class UnitController extends Controller
{
    use ValidationTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->may('create.item')) {
            return view('core::errors.restricted');
        }

        DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:unit,name'
        ]);

        $item_unit = new Unit;
        $item_unit->name = \Input::get('name');
        $item_unit->created_by = auth()->user()->id;
        $item_unit->updated_by = auth()->user()->id;

        if (!$item_unit->save()) {
            gritter_error("Unit " . $item_unit->name . " is not added");
            return redirect()->back();
        }

        DB::commit();

        gritter_success("Unit " . $item_unit->name . " has been added");
        timeline_publish('create.item-unit', "added unit " . $item_unit->name);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->may('read.item')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.item.unit.index');
        $view->list_item_unit = Unit::paginate(100);
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
        if (!auth()->user()->may('update.item')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.item.unit.edit');
        $view->unit = Unit::find($id);
        $view->list_unit = Unit::paginate(100);
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
        if (!auth()->user()->may('update.item')) {
            return view('core::errors.restricted');
        }

        DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:unit,name,' . $id
        ]);

        $item_unit = Unit::find($id);
        $item_unit->name = $request->input('name');
        $item_unit->updated_by = auth()->user()->id;

        if (!$item_unit->save()) {
            gritter_error("Unit " . $item_unit->name . " is not added");
            return redirect()->back();
        }

        DB::commit();

        gritter_success("Unit " . $item_unit->name . " has been added");
        timeline_publish('create.item-unit', "added unit " . $item_unit->name);

        return redirect('master/item/unit_master');
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
            $item_unit = Unit::find(\Input::get('id'));
            $item_unit_deleted = $item_unit;
            $item_unit->delete();

            timeline_publish('delete.item-unit', "unit " . $item_unit->name . " has been removed");

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
            gritter_success('Delete Item "' . $item_unit->name . '" Success', false);
        }

        return $response;
    }

    public function _list()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $units = Unit::all();
        $item_unit = [];
        foreach ($units as $unit) {
            array_push($item_unit, ['text' => $unit->name, 'value' => $unit->name]);
        }

        $response = array(
            'lists' => $item_unit,
        );
        return response()->json($response);
    }

    public function _insert(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
        ]);

        $valid = false;
        $response = array('status' => 'failed');

        if (!$validator->fails()) {
            $valid = true;
        }

        $unit = new Unit;
        $unit->name = $_POST['name'];
        $unit->created_by = auth()->user()->id;
        $unit->updated_by = auth()->user()->id;

        $check = Unit::where('name', $_POST['name'])->first();

        if (count($check) < 1 && $valid === true) {
            $unit->save();
            $response = array(
                'status' => 'success',
                'code' => $unit->id,
                'name' => $unit->name,
            );
        }
        return response()->json($response);
    }
}

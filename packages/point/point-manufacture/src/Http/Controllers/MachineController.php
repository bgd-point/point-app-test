<?php

namespace Point\PointManufacture\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\PointManufacture\Helpers\MachineHelper;
use Point\PointManufacture\Models\Machine;

class MachineController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.manufacture.machine');

        $view = view('point-manufacture::app.manufacture.point.machine.index');
        $search = app('request')->input('search');
        $view->machine_list = Machine::search($search)->paginate(100);

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.manufacture.machine');
        $view = view('point-manufacture::app.manufacture.point.machine.create');
        $view->code = MachineHelper::getLastCode();

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
        $this->validate($request, [
            'code' => 'required|unique:point_manufacture_machine,code',
            'name' => 'required|unique:point_manufacture_machine,name'
        ]);

        DB::beginTransaction();

        $machine = new Machine;
        $machine->notes = app('request')->input('notes');
        $machine->name = app('request')->input('name');
        $machine->code = app('request')->input('code');
        $machine->created_by = auth()->user()->id;
        $machine->updated_by = auth()->user()->id;
        $machine->save();

        timeline_publish('create.point.manufacture.machine', $machine->name);

        DB::commit();

        gritter_success('create machine "' . $machine->name . '" success');
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
        access_is_allowed('read.point.manufacture.machine');

        $view = view('point-manufacture::app.manufacture.point.machine.show');
        $view->machine = Machine::find($id);
        $view->histories = History::show('point_manufacture_machine', $id);

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
        access_is_allowed('update.point.manufacture.machine');

        $view = view('point-manufacture::app.manufacture.point.machine.edit');
        $view->machine = Machine::find($id);

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
        $this->validate($request, [
            'name' => 'required|unique:point_manufacture_machine,name,' . $id
        ]);

        DB::beginTransaction();
        
        $machine = Machine::find($id);
        $machine->notes = app('request')->input('notes');
        $machine->code = app('request')->input('code');
        $machine->name = app('request')->input('name');
        $machine->updated_by = auth()->user()->id;
        $machine->save();

        timeline_publish('update.point.manufacture.machine', $machine->name);

        DB::commit();

        gritter_success('update machine "' . $machine->name . '" success');
        return redirect('manufacture/point/machine/' . $id);
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
            DB::beginTransaction();
            $machine = Machine::find(app('request')->input('id'));
            $machine->delete();
            timeline_publish('delete.point.manufacture.machine', $machine->name);
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
            gritter_success('Delete Machine "' . $machine->name . '" Success');
        }

        return $response;
    }
}

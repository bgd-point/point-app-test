<?php

namespace Point\PointManufacture\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\GritterHelper;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\PointManufacture\Models\Process;

class ProcessController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.manufacture.process');

        $view = view('point-manufacture::app.manufacture.point.process.index');
        $search = \Input::get('search');
        $view->process_list = Process::search($search)->paginate(100);

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.manufacture.process');

        $view = view('point-manufacture::app.manufacture.point.process.create');
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
        $process_data = count(Process::all());

        if (!client_has_addon('premium') && $process_data >= 1) {
            return redirect()->back()->withErrors('Basic Feature cannot insert more than one process')->withInput();
        }

        \DB::beginTransaction();

        $this->validate($request, [
            'name' => 'required|unique:point_manufacture_process,name'
        ]);

        $process = new Process;
        $process->notes = \Input::get('notes');
        $process->name = \Input::get('name');
        $process->created_by = \Auth::user()->id;
        $process->updated_by = \Auth::user()->id;
        if (!$process->save()) {
            GritterHelper::error('process Failed to Add', 'false');
            return redirect()->back();
        }

        timeline_publish('create.point.manufacture.process', $process->name);
        GritterHelper::success('process "' . $process->name . '" Success to Add', 'false');
        \DB::commit();

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
        access_is_allowed('read.point.manufacture.process');

        $view = view('point-manufacture::app.manufacture.point.process.show');
        $view->process = Process::find($id);
        $view->histories = History::show('point_manufacture_process', $id);

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
        access_is_allowed('update.point.manufacture.process');

        $view = view('point-manufacture::app.manufacture.point.process.edit');
        $view->process = Process::find($id);
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
        \DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:point_manufacture_process,name,' . $id
        ]);

        $process = Process::find($id);
        $process->notes = \Input::get('notes');
        $process->name = \Input::get('name');
        $process->updated_by = \Auth::user()->id;
        if (!$process->save()) {
            \GritterHelper::error('process Group Failed to Add', 'false');
            return redirect()->back();
        }

        timeline_publish('update.point.manufacture.process', $process->name);
        GritterHelper::success('process "' . $process->name . '" Success to Update', 'false');
        \DB::commit();

        return redirect('manufacture/point/process/' . $id);
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

        if (!$this->validatePassword(\Auth::user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        try {
            \DB::beginTransaction();
            $process = Process::find(\Input::get('id'));
            $process->delete();
            timeline_publish('delete.point.manufacture.process', $process->name);
            \DB::commit();
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
            \GritterHelper::success('Delete process "' . $process->name . '" Success', 'false');
        }

        return $response;
    }
}

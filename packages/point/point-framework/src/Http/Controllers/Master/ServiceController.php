<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Service as ServiceMaster;
use Point\Framework\Models\Master\Service;

class ServiceController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.service');

        $view = view('framework::app.master.service.index');
        $view->list_service = ServiceMaster::search(\Input::get('status'), \Input::get('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.service');

        $view = view('framework::app.master.service.create');
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
        access_is_allowed('create.service');

        $this->validate($request, [
            'name' => 'required|unique:service',
            'price' => 'required',
        ]);

        DB::beginTransaction();

        $service = new ServiceMaster;
        $service->name = $request->input('name');
        $service->price = number_format_db($request->input('price'));
        $service->notes = $request->input('notes');
        $service->created_by = auth()->user()->id;
        $service->updated_by = auth()->user()->id;
        $service->save();

        timeline_publish('create.service', 'Success create a service '.$service->name);
        DB::commit();

        gritter_success('Success create new service');
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
        access_is_allowed('read.service');

        $view = view('framework::app.master.service.show');
        $view->service = ServiceMaster::find($id);
        $view->histories = History::show('service', $id);

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
        access_is_allowed('update.service');

        $view = view('framework::app.master.service.edit');
        $view->service = ServiceMaster::find($id);
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
        access_is_allowed('update.service');

        $this->validate($request, [
            'name' => 'required',
            'price' => 'required',
        ]);

        DB::beginTransaction();
        $service = ServiceMaster::find($id);
        $service->name = $request->input('name');
        $service->price = number_format_db($request->input('price'));
        $service->notes = $request->input('notes');
        $service->created_by = auth()->user()->id;
        $service->updated_by = auth()->user()->id;
        $service->save();

        timeline_publish('create.service', 'Success update a service '.$service->name);
        DB::commit();

        gritter_success('Success update a service '.$service->name);
        return redirect('master/service/' . $id);
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
            $service = ServiceMaster::find(\Input::get('id'));
            $service->delete();

            timeline_publish('delete.service', trans('framework::framework/master.service.delete.timeline', ['name' => $service->name]));

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
            gritter_error(trans('framework::framework/master.service.delete.failed', ['name' => $service->name]));
        }

        return $response;
    }

    public function _list()
    {
        $services = Service::all();
        $list_service = [];
        foreach ($services as $service) {
            array_push($list_service, ['text' => $service->name, 'value' => $service->id]);
        }
        $response = array(
            'lists' => $list_service
        );
        return response()->json($response);
    }

    public function _getPrice()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $service = Service::find(\Input::get('service_id'));
        return $service->price;
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.service')) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $service = Service::find($request->input('index'));

        if (!$service) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $service->disabled = $service->disabled == 0 ? 1 : 0;
        $service->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $service->disabled);

        return response()->json($response);
    }
}

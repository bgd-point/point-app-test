<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\MasterBank;

class MasterBankController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bank');

        $view = view('framework::app.master.bank.index');
        $view->list_bank = MasterBank::all();

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bank');

        $view = view('framework::app.master.bank.create');
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
        access_is_allowed('create.bank');

        $this->validate($request, [
            'name' => 'required|unique:bank',
            'price' => 'required',
        ]);

        DB::beginTransaction();

        $bank = new MasterBank;
        $bank->name = $request->input('name');
        $bank->created_by = auth()->user()->id;
        $bank->updated_by = auth()->user()->id;
        $bank->save();

        timeline_publish('create.bank', 'Success create a bank '.$bank->name);
        DB::commit();

        gritter_success('Success create new bank');
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
        access_is_allowed('read.bank');

        $view = view('framework::app.master.bank.show');
        $view->bank = MasterBank::find($id);

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
        access_is_allowed('update.bank');

        $view = view('framework::app.master.bank.edit');
        $view->bank = MasterBank::find($id);
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
        access_is_allowed('update.bank');

        $this->validate($request, [
            'name' => 'required|unique:bank',
            'price' => 'required',
        ]);

        DB::beginTransaction();
        $bank = MasterBank::find($id);
        $bank->name = $request->input('name');
        $bank->created_by = auth()->user()->id;
        $bank->updated_by = auth()->user()->id;
        $bank->save();

        timeline_publish('create.bank', 'Success update a bank '.$bank->name);
        DB::commit();

        gritter_success('Success update a bank '.$bank->name);
        return redirect('master/bank/' . $id);
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
            $bank = MasterBank::find(\Input::get('id'));
            $bank->delete();

            timeline_publish('delete.bank', trans('framework::framework/master.bank.delete.timeline', ['name' => $bank->name]));

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
            gritter_error(trans('framework::framework/master.bank.delete.failed', ['name' => $bank->name]));
        }

        return $response;
    }
}

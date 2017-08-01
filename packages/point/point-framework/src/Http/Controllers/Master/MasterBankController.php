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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function _store(Request $request)
    {
        access_is_allowed('create.bank');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|unique:bank',
        ]);

        $response = array('status' => 'failed');

        if ($validator->fails()) {
            return response()->json($response);
        }

        DB::beginTransaction();

        $bank = new MasterBank;
        $bank->name = $request->input('name');
        $bank->created_by = auth()->user()->id;
        $bank->updated_by = auth()->user()->id;
        $bank->save();

        timeline_publish('create.bank', 'Success create a bank '.$bank->name);
        DB::commit();

        $view = view('framework::app.master.bank._data');
        $view->list_bank = MasterBank::all();
        return $view;
    }

    public function _update(Request $request)
    {
        access_is_allowed('update.bank');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
            'bank_id' => 'required',
        ]);

        $response = array('status' => 'failed');

        if ($validator->fails()) {
            return response()->json($response);
        }

        DB::beginTransaction();

        $bank = MasterBank::find(\Input::get('bank_id'));
        $bank->name = $request->input('name');
        $bank->created_by = auth()->user()->id;
        $bank->updated_by = auth()->user()->id;
        $bank->save();

        timeline_publish('create.bank', 'Success update a bank '.$bank->name);
        DB::commit();

        $view = view('framework::app.master.bank._data');
        $view->list_bank = MasterBank::all();
        return $view;
    }

    public function _delete($id)
    {
        access_is_allowed('delete.bank');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $bank = MasterBank::find($id)->delete();
        $view = view('framework::app.master.bank._data');
        $view->list_bank = MasterBank::all();
        return $view;
    }
}

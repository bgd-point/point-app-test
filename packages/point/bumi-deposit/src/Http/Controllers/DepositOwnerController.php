<?php

namespace Point\BumiDeposit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Point\Core\Traits\ValidationTrait;
use Point\Core\Models\Master\History;
use Point\Framework\Http\Controllers\Controller;
use Point\BumiDeposit\Models\DepositOwner;

class DepositOwnerController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.deposit.owner');

        return view('bumi-deposit::app.facility.bumi-deposit.owner.index', array(
            'owners'=> DepositOwner::search(\Input::get('search'))->paginate(100)
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.deposit.owner');

        return view('bumi-deposit::app.facility.bumi-deposit.owner.create');
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
            'name' => 'required',
        ]);

        DB::beginTransaction();

        $owner = new DepositOwner;
        $owner->name = $request->input('name');
        $owner->notes = $request->input('notes');
        $owner->save();
        timeline_publish('create.bumi.deposit.owner', 'create owner "'. $owner->name .'"');
        DB::commit();

        gritter_success('create owner success');
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
        access_is_allowed('read.bumi.deposit.owner');

        $owner = DepositOwner::find($id);
        return view('bumi-deposit::app.facility.bumi-deposit.owner.show', array(
            'owner' => $owner,
            'histories' => History::show($owner->getTable(), $id)
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.bumi.deposit.owner');

        return view('bumi-deposit::app.facility.bumi-deposit.owner.edit', array(
            'owner' => DepositOwner::find($id)
        ));
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
        access_is_allowed('update.bumi.deposit.owner');

        $this->validate($request, [
            'name' => 'required',
        ]);

        $owner = DepositOwner::find($id);
        $owner->name = $request->input('name');
        $owner->notes = $request->input('notes');
        $owner->save();
        timeline_publish('update.bumi.deposit.owner', 'update owner "'. $owner->name .'"');

        gritter_success('update owner success');
        return redirect('facility/bumi-deposit/owner/'.$owner->id);
    }

    public function delete()
    {
        access_is_allowed('delete.bumi.deposit.owner');

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
            $owner = DepositOwner::find(\Input::get('id'));
            $owner->delete();
            timeline_publish('delete.bumi.deposit.owner', 'delete owner "'. $owner->name .'"');
            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'delete owner success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('delete owner "'. $owner->owner_name .'" success');
        }

        return $response;
    }
}

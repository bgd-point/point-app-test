<?php

namespace Point\BumiDeposit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Point\Core\Traits\ValidationTrait;
use Point\Core\Models\Master\History;
use Point\Framework\Http\Controllers\Controller;
use Point\BumiDeposit\Models\DepositGroup;

class DepositGroupController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.deposit.group');

        return view('bumi-deposit::app.facility.bumi-deposit.group.index', array(
            'groups'=> DepositGroup::search(\Input::get('search'))->orderBy('name')->paginate(100)
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.deposit.group');

        return view('bumi-deposit::app.facility.bumi-deposit.group.create');
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

        $group = new DepositGroup;
        $group->name = $request->input('name');
        $group->notes = $request->input('notes');
        $group->save();
        timeline_publish('create.bumi.deposit.group', 'create group "'. $group->name .'"');
        DB::commit();

        gritter_success('create group success');
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
        access_is_allowed('read.bumi.deposit.group');

        $group = DepositGroup::find($id);
        return view('bumi-deposit::app.facility.bumi-deposit.group.show', array(
            'group' => $group,
            'histories' => History::show($group->getTable(), $id)
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
        access_is_allowed('update.bumi.deposit.group');

        return view('bumi-deposit::app.facility.bumi-deposit.group.edit', array(
            'group' => DepositGroup::find($id)
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
        access_is_allowed('update.bumi.deposit.group');

        $this->validate($request, [
            'name' => 'required',
        ]);

        $group = DepositGroup::find($id);
        $group->name = $request->input('name');
        $group->notes = $request->input('notes');
        timeline_publish('update.bumi.deposit.group', 'update group "'. $group->name .'"');
        $group->save();

        gritter_success('update group success');
        return redirect('facility/bumi-deposit/group/'.$group->id);
    }

    public function delete()
    {
        access_is_allowed('delete.bumi.deposit.group');

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
            $group = DepositGroup::find(\Input::get('id'));
            $group->delete();
            timeline_publish('delete.bumi.deposit.group', 'delete group "'. $group->name .'"');
            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'delete group success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('delete group "'. $group->group_name .'" success');
        }

        return $response;
    }
}

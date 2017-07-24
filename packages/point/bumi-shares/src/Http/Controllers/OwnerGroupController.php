<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\BumiShares\Models\OwnerGroup;
use App\Http\Controllers\Controller;

class OwnerGroupController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.shares.owner.group');

        $view = view('bumi-shares::app.facility.bumi-shares.owner-group.index');
        $view->owner_group_list = OwnerGroup::search(app('request')->input('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.shares.owner.group');

        $view = view('bumi-shares::app.facility.bumi-shares.owner-group.create');
        return $view;
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
            'name' => 'required|unique:bumi_shares_owner_group'
        ]);

        DB::beginTransaction();
        $owner_group = new OwnerGroup;
        $owner_group->notes = app('request')->input('notes');
        $owner_group->name = app('request')->input('name');
        $owner_group->created_by = auth()->user()->id;
        $owner_group->updated_by = auth()->user()->id;
        $owner_group->save();
        timeline_publish('create.bumi.shares.owner.group', 'create shares group "'. $owner_group->name .'"');
        DB::commit();

        gritter_success('create group "'. $owner_group->name .'" success');
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
        access_is_allowed('read.bumi.shares.owner.group');

        $view = view('bumi-shares::app.facility.bumi-shares.owner-group.show');
        $view->owner_group = OwnerGroup::find($id);
        $view->histories = History::show('bumi_shares_owner_group', $id);
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.bumi.shares.owner.group');

        $view = view('bumi-shares::app.facility.bumi-shares.owner-group.edit');
        $view->owner_group = OwnerGroup::find($id);
        return $view;
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
        $this->validate($request, [
            'name' => 'required|unique:bumi_shares_owner_group,name,'.$id
        ]);

        DB::beginTransaction();
        $owner_group = OwnerGroup::find($id);
        $owner_group->notes = app('request')->input('notes');
        $owner_group->name = app('request')->input('name');
        $owner_group->updated_by = auth()->user()->id;
        $owner_group->save();
        timeline_publish('update.bumi.shares.owner.group', 'update shares group "'. $owner_group->name .'"');
        DB::commit();

        gritter_success('update group "'. $owner_group->name .'" success');
        return redirect('facility/bumi-shares/owner-group/'.$id);
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
            $owner_group = OwnerGroup::find(app('request')->input('id'));
            $owner_group->delete();
            timeline_publish('delete.bumi.shares.owner.group', 'delete group "'. $owner_group->name .'"');
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
            gritter_success('delete shares group "'. $owner_group->name .'" success');
        }

        return $response;
    }
}

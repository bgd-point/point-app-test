<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\BumiShares\Models\Owner;
use App\Http\Controllers\Controller;

class OwnerController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.shares.owner');

        $view = view('bumi-shares::app.facility.bumi-shares.owner.index');
        $view->owner_list = Owner::search(app('request')->input('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.shares.owner');

        $view = view('bumi-shares::app.facility.bumi-shares.owner.create');
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
            'name' => 'required|unique:bumi_shares_owner'
        ]);

        DB::beginTransaction();
        $owner = new Owner;
        $owner->notes = app('request')->input('notes');
        $owner->name = app('request')->input('name');
        $owner->created_by = auth()->user()->id;
        $owner->updated_by = auth()->user()->id;
        $owner->save();
        timeline_publish('create.bumi.shares.owner', 'create shares owner "'. $owner->name .'"');
        DB::commit();

        gritter_success('create owner "'. $owner->name .'" success');
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
        access_is_allowed('read.bumi.shares.owner');

        $view = view('bumi-shares::app.facility.bumi-shares.owner.show');
        $view->owner = Owner::find($id);
        $view->histories = History::show('bumi_shares_owner', $id);
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
        access_is_allowed('update.bumi.shares.owner');

        $view = view('bumi-shares::app.facility.bumi-shares.owner.edit');
        $view->owner = Owner::find($id);
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
            'name' => 'required|unique:bumi_shares_owner,name,'.$id
        ]);

        DB::beginTransaction();
        $owner = Owner::find($id);
        $owner->notes = app('request')->input('notes');
        $owner->name = app('request')->input('name');
        $owner->updated_by = auth()->user()->id;
        $owner->save();
        timeline_publish('update.bumi.shares.owner', 'update shares owner "'. $owner->name .'"');
        DB::commit();

        gritter_success('update owner "'. $owner->name .'" success');
        return redirect('facility/bumi-shares/owner/'.$id);
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
            $owner = Owner::find(app('request')->input('id'));
            $owner->delete();
            timeline_publish('delete.bumi.shares.owner', 'delete owner "'. $owner->name .'"');
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
            gritter_success('delete shares owner "'. $owner->name .'" success');
        }

        return $response;
    }
}

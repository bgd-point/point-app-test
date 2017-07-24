<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\BumiShares\Models\Shares;
use App\Http\Controllers\Controller;

class SharesController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.shares');

        $view = view('bumi-shares::app.facility.bumi-shares.shares.index');
        $view->shares_list = Shares::search(app('request')->input('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.shares');

        $view = view('bumi-shares::app.facility.bumi-shares.shares.create');
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
            'name' => 'required|unique:bumi_shares'
        ]);

        DB::beginTransaction();
        $shares = new Shares;
        $shares->notes = app('request')->input('notes');
        $shares->name = app('request')->input('name');
        $shares->created_by = auth()->user()->id;
        $shares->updated_by = auth()->user()->id;
        $shares->save();
        timeline_publish('create.bumi.shares', 'create shares "'. $shares->name .'"');
        DB::commit();

        gritter_success('create shares "'. $shares->name .'" success');
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
        access_is_allowed('read.bumi.shares');

        $view = view('bumi-shares::app.facility.bumi-shares.shares.show');
        $view->shares = Shares::find($id);
        $view->histories = History::show('bumi_shares', $id);
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
        access_is_allowed('update.bumi.shares');

        $view = view('bumi-shares::app.facility.bumi-shares.shares.edit');
        $view->shares = Shares::find($id);
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
            'name' => 'required|unique:bumi_shares,name,'.$id
        ]);

        DB::beginTransaction();
        $shares = Shares::find($id);
        $shares->notes = app('request')->input('notes');
        $shares->name = app('request')->input('name');
        $shares->updated_by = auth()->user()->id;
        $shares->save();
        timeline_publish('update.bumi.shares', 'update shares "'. $shares->name .'"');
        DB::commit();

        gritter_success('update shares "'. $shares->name .'" success');
        return redirect('facility/bumi-shares/shares/'.$id);
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
            $shares = Shares::find(app('request')->input('id'));
            $shares->delete();
            timeline_publish('delete.bumi.shares', 'delete shares "'. $shares->name .'"');
            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'delete shares success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('delete shares "'. $shares->name .'" success');
        }

        return $response;
    }
}

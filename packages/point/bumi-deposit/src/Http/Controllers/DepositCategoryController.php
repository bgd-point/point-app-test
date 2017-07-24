<?php

namespace Point\BumiDeposit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Point\Core\Traits\ValidationTrait;
use Point\Core\Models\Master\History;
use Point\Framework\Http\Controllers\Controller;
use Point\BumiDeposit\Models\DepositCategory;

class DepositCategoryController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.deposit.category');

        return view('bumi-deposit::app.facility.bumi-deposit.category.index', array(
            'categorys'=> DepositCategory::search(\Input::get('search'))->orderBy('name')->paginate(100)
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.bumi.deposit.category');

        return view('bumi-deposit::app.facility.bumi-deposit.category.create');
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

        $category = new DepositCategory;
        $category->name = $request->input('name');
        $category->notes = $request->input('notes');
        $category->save();
        timeline_publish('create.bumi.deposit.category', 'create category "'. $category->name .'"');
        DB::commit();

        gritter_success('create category success');
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
        access_is_allowed('read.bumi.deposit.category');

        $category = DepositCategory::find($id);
        return view('bumi-deposit::app.facility.bumi-deposit.category.show', array(
            'category' => $category,
            'histories' => History::show($category->getTable(), $id)
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
        access_is_allowed('update.bumi.deposit.category');

        return view('bumi-deposit::app.facility.bumi-deposit.category.edit', array(
            'category' => DepositCategory::find($id)
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
        access_is_allowed('update.bumi.deposit.category');

        $this->validate($request, [
            'name' => 'required',
        ]);

        $category = DepositCategory::find($id);
        $category->name = $request->input('name');
        $category->notes = $request->input('notes');
        timeline_publish('update.bumi.deposit.category', 'update category "'. $category->name .'"');
        $category->save();

        gritter_success('update category success');
        return redirect('facility/bumi-deposit/category/'.$category->id);
    }

    public function delete()
    {
        access_is_allowed('delete.bumi.deposit.category');

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
            $category = DepositCategory::find(\Input::get('id'));
            $category->delete();
            timeline_publish('delete.bumi.deposit.category', 'delete category "'. $category->name .'"');
            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'delete category success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('delete category "'. $category->category_name .'" success');
        }

        return $response;
    }
}

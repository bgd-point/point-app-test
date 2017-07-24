<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\ItemCategory;
use Point\Framework\Models\Master\ItemUmum;

class ItemCategoryController extends Controller
{
    use ValidationTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->may('create.item')) {
            return view('core::errors.restricted');
        }

        DB::beginTransaction();
        $this->validate($request, [
            'code' => 'required|unique:item_category,code',
            'name' => 'required|unique:item_category,name'
        ]);

        $item_category = new ItemCategory;
        $item_category->code = \Input::get('code');
        $item_category->name = \Input::get('name');
        $item_category->created_by = auth()->user()->id;
        $item_category->updated_by = auth()->user()->id;

        if (!$item_category->save()) {
            gritter_error(trans('framework::framework/master.item_category.create.failed', ['name' => $item_category->name]), false);
            return redirect()->back();
        }

        DB::commit();

        gritter_success(trans('framework::framework/master.item_category.create.success', ['name' => $item_category->name]), false);

        timeline_publish('create.item-category', trans('framework::framework/master.item_category.create.timeline', ['name' => $item_category->name]));

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->may('read.item')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.item.category.index');
        $view->list_item_category = ItemCategory::paginate(100);
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
        if (!auth()->user()->may('update.item')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.item.category.edit');
        $view->item_category = ItemCategory::find($id);
        $view->list_item_category = ItemCategory::paginate(100);
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
        if (!auth()->user()->may('update.item')) {
            return view('core::errors.restricted');
        }

        DB::beginTransaction();
        $this->validate($request, [
            'code' => 'required|unique:item_category,code,' . $id,
            'name' => 'required|unique:item_category,name,' . $id
        ]);

        $item_category = ItemCategory::find($id);
        $item_category->code = $request->input('code');
        $item_category->name = $request->input('name');
        $item_category->updated_by = auth()->user()->id;

        if (!$item_category->save()) {
            gritter_error(trans('framework::framework/master.item_category.update.failed', ['name' => $item_category->name]), false);
            return redirect()->back();
        }

        DB::commit();

        gritter_success(trans('framework::framework/master.item_category.update.success', ['name' => $item_category->name]), false);

        timeline_publish('update.item-category', trans('framework::framework/master.item_category.update.timeline', ['name' => $item_category->name]));

        return redirect('master/item/category');
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
            $item_category = ItemCategory::find(\Input::get('id'));
            $item_category_deleted = $item_category;
            $item_category->delete();

            timeline_publish('delete.item-category', trans('framework::framework/master.item_category.delete.timeline', ['name' => $item_category->name]));

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
            gritter_success('Delete Item "' . $item->name . '" Success', false);
        }

        return $response;
    }

    public function _list()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $categories = ItemCategory::all();
        $item_category = [];
        foreach ($categories as $category) {
            array_push($item_category, ['text' => $category->name, 'value' => $category->id]);
        }

        $response = array(
            'lists' => $item_category
        );
        return response()->json($response);
    }

    public function _insert(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required',
        ]);

        $valid = false;
        $response = array('status' => 'failed');

        if (!$validator->fails()) {
            $valid = true;
        }

        $item_category = new ItemCategory;
        $item_category->code = $_POST['code'];
        $item_category->name = $_POST['name'];
        $item_category->created_by = auth()->user()->id;
        $item_category->updated_by = auth()->user()->id;

        $response = array('status' => 'failed');
        $check = ItemCategory::where('code', $_POST['code'])->first();

        if (count($check) < 1 && $valid === true) {
            $item_category->save();
            $response = array(
                'status' => 'success',
                'code' => $item_category->id,
                'name' => $item_category->name,
            );
        }

        return response()->json($response);
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.item')) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $item_category = ItemCategory::find($request->input('index'));

        if (!$item_category) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $item_category->disabled = $item_category->disabled == 0 ? 1 : 0;
        $item_category->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $item_category->disabled);

        return response()->json($response);
    }
}

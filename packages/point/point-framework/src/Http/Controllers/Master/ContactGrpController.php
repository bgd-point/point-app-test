<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;

class ContactGrpController extends Controller
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
        $person_type = PersonType::find(\Input::get('person_type_id'));
        access_is_allowed('create.' . $person_type->slug);

        DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:person_group,name'
        ]);

        $person_group = new PersonGroup;
        $person_group->person_type_id = \Input::get('person_type_id');
        $person_group->name = \Input::get('name');
        $person_group->created_by = auth()->user()->id;
        $person_group->updated_by = auth()->user()->id;

        if (!$person_group->save()) {
            gritter_error(trans('framework::framework/master.person_group.create.failed', ['name' => $person_group->name]), false);
            return redirect()->back();
        }

        DB::commit();

        gritter_success(trans('framework::framework/master.person_group.create.success', ['name' => $person_group->name]), false);

        // TODO new timeline

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function index($person_type_slug)
    {
        access_is_allowed('read.' . $person_type_slug);

        $person_type = PersonHelper::getType($person_type_slug);

        $view = view('framework::app.master.contact.group.index');
        $view->person_type = $person_type;
        $view->list_person_group = PersonGroup::where('person_type_id', '=', $person_type->id)->paginate(100);
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($person_type_slug, $id)
    {
        access_is_allowed('update.' . $person_type_slug);

        $person_type = PersonHelper::getType($person_type_slug);

        $view = view('framework::app.master.contact.group.edit');
        $view->person_group = PersonGroup::find($id);
        $view->person_type = $person_type;
        $view->list_person_group = PersonGroup::where('person_type_id', '=', $person_type->id)->paginate(100);
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $person_type_slug, $id)
    {
        access_is_allowed('update.' . $person_type_slug);

        DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:person_group,name,' . $id
        ]);

        $person_group = PersonGroup::find($id);
        $person_group->name = $request->input('name');
        $person_group->updated_by = auth()->user()->id;

        if (!$person_group->save()) {
            gritter_error(trans('framework::framework/master.person_group.update.failed', ['name' => $person_group->name]), false);
            return redirect()->back();
        }

        DB::commit();

        gritter_success(trans('framework::framework/master.person_group.update.success', ['name' => $person_group->name]), false);

        // TODO new timeline

        return redirect()->back();
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
            $person_group = PersonGroup::find(\Input::get('id'));
            $person_group->delete();

            timeline_publish('delete.item-category', trans('framework::framework/master.person_group.delete.timeline', ['name' => $person_group->name]));

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

    public function _list($person_type_id)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $categories = PersonGroup::where('person_type_id', '=', $person_type_id)->get();
        $person_group = [];
        foreach ($categories as $category) {
            array_push($person_group, ['text' => $category->name, 'value' => $category->id]);
        }

        $response = array(
            'lists' => $person_group
        );
        return response()->json($response);
    }
}

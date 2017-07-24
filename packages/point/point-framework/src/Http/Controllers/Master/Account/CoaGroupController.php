<?php

namespace Point\Framework\Http\Controllers\Master\Account;

use Illuminate\Http\Request;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\CoaGroup;

class CoaGroupController extends Controller
{
    use ValidationTrait;

    public function _store(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'coa_category_id' => 'required'
        ]);

        $response = array('status' => 'failed');

        if ($validator->fails()) {
            return response()->json($response);
        }

        $coa_group = new CoaGroup;
        $coa_group->coa_number = \Input::get('number') ?: null;
        $coa_group->coa_category_id = \Input::get('coa_category_id');
        $coa_group->name = \Input::get('name');
        $coa_group->created_by = auth()->user()->id;
        $coa_group->updated_by = auth()->user()->id;


        $count_name = CoaGroup::where('name', \Input::get('name'))->count();
        $count_number = CoaGroup::where('coa_number', \Input::get('number'))->count();
        if (!$count_number && !$count_name) {
            $coa_group->save();
            $response = array('status' => 'success');
        }


        return response()->json($response);
    }

    public function _show()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $view = view('framework::app.master.coa.group._show');
        $view->group = CoaGroup::find(\Input::get('id'));

        return $view;
    }

    public function _edit()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $group = CoaGroup::find(\Input::get('group_id'));

        return response()->json([
            'coa_category_id' => $group->coa_category_id,
            'coa_category_name' => $group->category->name,
            'coa_number' => $group->coa_number,
            'name' => $group->name
        ]);
    }

    public function _update(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        $response = array('status' => 'failed');

        if ($validator->fails()) {
            return response()->json($response);
        }

        $coa_group = CoaGroup::find(\Input::get('group_id'));
        ;
        $coa_group->coa_number = \Input::get('number') ?: null;
        $coa_group->name = \Input::get('name');
        $coa_group->created_by = auth()->user()->id;
        $coa_group->updated_by = auth()->user()->id;
        $coa_group->save();
        $response = array('status' => 'success');

        return response()->json($response);
    }

    public function delete($id)
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
            $coa_group = CoaGroup::find($id);
            $coa_group->delete();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'target' => 'coa',
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Delete Success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('Delete Coa "' . $coa_group->name . '" Success', false);
        }

        return $response;
    }
}

<?php

namespace Point\Framework\Http\Controllers\Master\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Models\Master\CoaCategory;

class CoaCategoryController extends Controller
{
    use ValidationTrait;

    public function store(Request $request)
    {
        access_is_allowed('create.coa');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
            'group_id'=>'required',
            'position_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(array('status' => 'failed'));
        }

        $check = CoaCategory::where('name', $request->get('name'))->first();
        if ($check) {
            return response()->json(array('status' => 'failed'));
        }

        $category = new CoaCategory;
        $category->coa_position_id = $request->get('position_id');
        $category->coa_group_category_id = $request->get('group_id');
        $category->name = $request->get('name');
        $category->save();

        return response()->json(array(
            'status' => 'success',
            'id'=> $category->id,
            'name'=> $category->name,
        ));
    }

    public function delete($id)
    {
        access_is_allowed('delete.coa');
        
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
            $coa = CoaCategory::find($id);
            $coa->delete();
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
            gritter_success(trans('framework::framework/master.coa.delete.success'));
        }

        return $response;
    }
}

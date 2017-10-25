<?php

namespace Point\Framework\Http\Controllers\Master\Account;

use Illuminate\Http\Request;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\AccountDepreciation;
use Point\Framework\Models\FixedAsset;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\Master\CoaGroup;
use Point\Framework\Models\Master\CoaGroupCategory;
use Point\Framework\Models\Master\CoaPosition;
use Point\Framework\Models\Master\Item;

class CoaController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('framework::app.master.coa.index');
        $view->list_coa_position = CoaPosition::all();
        $view->list_coa_group_category = CoaGroupCategory::all();
        $view->search = \Input::get('search');
        return $view;
    }

    /**
     * Display a data listing of the resource
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function _loadIndex()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $view = view('framework::app.master.coa._data-index');
        $view->list_coa_position = CoaPosition::all();
        return $view;
    }

    public function _listAsset()
    {
        $selected_coa = Coa::all();
        $coa_array = [];
        foreach ($selected_coa as $coa) {
            array_push($coa_array, ['text' => $coa->account, 'value' => $coa->id]);
        }

        $response = array(
            'lists' => $coa_array
        );
        return response()->json($response);
    }

    public function _listSalesIncome()
    {
        $selected_coa = Coa::all();
        $coa_array = [];
        foreach ($selected_coa as $coa) {
            array_push($coa_array, ['text' => $coa->account, 'value' => $coa->id]);
        }

        $response = array(
            'lists' => $coa_array
        );
        return response()->json($response);
    }

    public function _listExpense()
    {
        $selected_coa = Coa::all();
        $coa_array = [];
        foreach ($selected_coa as $coa) {
            array_push($coa_array, ['text' => $coa->account, 'value' => $coa->id]);
        }

        $response = array(
            'lists' => $coa_array
        );
        return response()->json($response);
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.coa')) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $coa = Coa::find($request->input('index'));

        if (!$coa) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $coa->disabled = $coa->disabled == 0 ? 1 : 0;
        $coa->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $coa->disabled);

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
            $coa = Coa::find($id);
            $coa->delete();
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
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

    public function _insert(Request $request)
    {
        access_is_allowed('create.coa');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        $valid = false;
        $response = array('status' => 'failed');

        if (!$validator->fails()) {
            $valid = true;
        }

        $coa = new Coa;
        $coa->name = $_POST['name'];
        $coa->coa_category_id = CoaCategory::where('name', 'Inventories')->first()->id;
        $coa->created_by = auth()->user()->id;
        $coa->updated_by = auth()->user()->id;
        $coa->has_subledger = true;
        $coa->subledger_type = get_class(new Item);

        $check = Coa::where('name', $_POST['name'])->first();

        if (count($check) < 1 && $valid === true) {
            $coa->save();
            $response = array(
                'status' => 'success',
                'code' => $coa->id,
                'name' => $coa->name,
            );
        }
        return response()->json($response);
    }

    // COA

    public function _insertByGroup(Request $request)
    {
        access_is_allowed('create.coa');

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

        $coa = new Coa;
        $coa->name = \Input::get('name');
        $coa->coa_number = \Input::get('number') != '' ? \Input::get('number') : null;
        $coa->coa_category_id = \Input::get('category_id');
        $coa->coa_group_id = \Input::get('group_id');
        $coa->has_subledger = \Input::get('has_subledger') ? \Input::get('has_subledger') : '0';
        $coa->subledger_type = $this->getClassSubledgerType(\Input::get('subledger_type'));
        $coa->created_by = auth()->user()->id;
        $coa->updated_by = auth()->user()->id;

        $count_name = Coa::where('name', \Input::get('name'))->count();

        $using_coa_number = false;
        $count_number = 0;
        if (\Input::get('number') != null) {
            $count_number = Coa::where('coa_number', \Input::get('number'))->get()->count();
            $using_coa_number = true;
        }

        if ($count_name || ($using_coa_number === true && $count_number)) {
            return response()->json($response);
        }
        
        $coa->save();
        $response = array(
            'status' => 'success', 'type' => \Input::get('subledger_type')
        );

        self::storeFixedAsset($coa, $request);
        return response()->json($response);
    }

    private function getClassSubledgerType($subledger_type = '')
    {
        if ($subledger_type == 'item') {
            return 'Point\Framework\Models\Master\Item';
        } elseif ($subledger_type == 'person') {
            return 'Point\Framework\Models\Master\Person';
        } elseif ($subledger_type == 'fixed_asset') {
            return 'Point\Framework\Models\FixedAsset';
        }

        return null;
    }

    public function storeFixedAsset($coa)
    {
        if (\Input::get('subledger_type') == 'fixed_asset' && Coa::joinCategory()->where('coa_category.name', 'Fixed Assets')->selectOriginal()->first()) {
            $fixed_asset = new FixedAsset;
            $fixed_asset->account_id = $coa->id;
            $fixed_asset->useful_life = \Input::get('useful_life');
            $fixed_asset->save();
        }
    }

    public function _insertByCategory(Request $request)
    {
        access_is_allowed('create.coa');

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

        $coa = new Coa;
        $coa->name = \Input::get('name');
        $coa->coa_number = \Input::get('coa_number') != '' ? \Input::get('coa_number') : null;
        $coa->coa_category_id = \Input::get('category_id');
        $coa->has_subledger = \Input::get('has_subledger') && $this->getClassSubledgerType(\Input::get('subledger_type')) ? true : false;
        $coa->subledger_type = $this->getClassSubledgerType(\Input::get('subledger_type'));
        $coa->created_by = auth()->user()->id;
        $coa->updated_by = auth()->user()->id;

        $count_name = Coa::where('name', \Input::get('name'))->get()->count();
        $using_coa_number = false;
        $count_number = 0;
        if (\Input::get('coa_number') != null) {
            $count_number = Coa::where('coa_number', \Input::get('coa_number'))->get()->count();
            $using_coa_number = true;
        }

        if ($count_name || ($using_coa_number === true && $count_number)) {
            return response()->json($response);
        }

        $coa->save();
        $response = array('status' => 'success');

        self::storeFixedAsset($coa, $request);
        return response()->json($response);
    }

    public function _edit()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $view = view('framework::app.master.coa._form-edit');
        $view->coa = Coa::find(\Input::get('coa_id'));
        $view->list_group = CoaGroup::where('coa_category_id', $view->coa->coa_category_id)->get();
        return $view;
    }

    public function _updateByCategory(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name_coa_edit' => 'required|string',
        ]);

        $response = array('status' => 'failed');

        if ($validator->fails()) {
            return response()->json($response);
        }

        $coa = Coa::find(\Input::get('coa_id'));
        $coa->name = \Input::get('name_coa_edit');
        $coa->coa_number = \Input::get('number_coa_edit') ? \Input::get('number_coa_edit') : null;
        $coa->coa_group_id = $request->input('group_id') ? \Input::get('group_id') : null;
        $coa->has_subledger = \Input::get('has_subledger') ? true : false;
        $coa->subledger_type = \Input::get('has_subledger') ? $this->getClassSubledgerType(\Input::get('subledger_type')) : '';
        $check = Coa::where('name', \Input::get('name_coa_edit'))->whereNotIn('id', [\Input::get('coa_id')])->get()->count();
        if ($check) {
            return response()->json($response);
        }

        $coa->save();

        if ((\Input::get('useful_life') > 0) && (\Input::get('has_subledger') == 'on') && (\Input::get('subledger_type') == 'fixed_asset')) {
            self::storeFixedAsset($coa, $request);
        }

        $response = array(
            'status' => 'success',
        );
        return response()->json($response);
    }

    public function _show()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $view = view('framework::app.master.coa._data-show');
        $view->coa = Coa::find(\Input::get('id'));

        return $view;
    }

    // FIXED ASSET

    public function _showCategory()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $view = view('framework::app.master.coa._data-show-category');
        $view->category = CoaCategory::find(\Input::get('id'));
        $view->account_depreciation = AccountDepreciation::get();
        return $view;
    }

    public function _listFixedAssetHasSubledger()
    {
        $selected_coa = Coa::joinCategory()
            ->where('coa_category.name', 'Fixed Assets')
            ->where('has_subledger', 1)
            ->selectOriginal()
            ->get();

        $coa_array = [];
        foreach ($selected_coa as $coa) {
            array_push($coa_array, ['text' => $coa->account, 'value' => $coa->id]);
        }

        $response = array(
            'lists' => $coa_array
        );
        return response()->json($response);
    }

    public function _listFixedAssetNotHasSubledger()
    {
        $selected_coa = Coa::joinCategory()
            ->where('coa_category.name', 'Fixed Assets')
            ->where('has_subledger', 0)
            ->selectOriginal()
            ->get();

        $coa_array = [];
        foreach ($selected_coa as $coa) {
            array_push($coa_array, ['text' => $coa->account, 'value' => $coa->id]);
        }

        $response = array(
            'lists' => $coa_array
        );
        return response()->json($response);
    }
}

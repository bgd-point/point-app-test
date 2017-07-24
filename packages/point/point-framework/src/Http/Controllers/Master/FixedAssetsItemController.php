<?php

namespace Point\Framework\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FixedAssetsItemHelper;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\FixedAssetsItem;

class FixedAssetsItemController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.fixed.assets.item');

        $view = view('framework::app.master.fixed-assets.index');
        $view->list_fixed_assets_item = FixedAssetsItem::search(\Input::get('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.fixed.assets.item');

        $view = view('framework::app.master.fixed-assets.create');
        $view->list_account_fixed_assets = Coa::joinCategory()
            ->where('coa_category.name', '=', 'Fixed Assets')
            ->hasSubledger()
            ->selectOriginal()
            ->get();

        $view->code = FixedAssetsItemHelper::getLastCode();

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
        access_is_allowed('create.fixed.assets.item');
        
        $this->validate($request, [
            'code' => 'required|unique:fixed_assets_item,code',
            'name' => 'required|unique:fixed_assets_item,name',
            'account_fixed_asset_id' => 'required',
            'useful_life' => 'required',
            'salvage_value' => 'required',
            'unit' => 'required'
        ]);

        \DB::beginTransaction();

        $fixed_assets_item = new FixedAssetsItem;
        $fixed_assets_item->code = $request->input('code');
        $fixed_assets_item->name = $request->input('name');
        $fixed_assets_item->account_asset_id = $request->input('account_fixed_asset_id');
        $fixed_assets_item->useful_life = number_format_db($request->input('useful_life'));
        $fixed_assets_item->salvage_value = number_format_db($request->input('salvage_value'));
        $fixed_assets_item->unit = $request->input('unit');
        $fixed_assets_item->created_by = auth()->user()->id;
        $fixed_assets_item->updated_by = auth()->user()->id;
        $fixed_assets_item->save();
        timeline_publish('create.fixed.assets.item', 'Success create new fixed assets item');
        \DB::commit();

        gritter_success('Success create new fixed assets item');
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
        access_is_allowed('read.fixed.assets.item');

        $view = view('framework::app.master.fixed-assets.show');
        $view->fixed_asset_item  = FixedAssetsItem::find($id);
        $view->histories = History::show('fixed_assets_item', $id);
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
        access_is_allowed('update.fixed.assets.item');
        
        $view = view('framework::app.master.fixed-assets.edit');

        $view->fixed_assets_item = FixedAssetsItem::find($id);
        $view->list_account_fixed_assets = Coa::joinCategory()
            ->where('coa_category.name', '=', 'Fixed Assets')
            ->hasSubledger()
            ->selectOriginal()
            ->get();
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
        access_is_allowed('create.fixed.assets.item');
        
        $this->validate($request, [
            'code' => 'required',
            'name' => 'required',
            'account_fixed_asset_id' => 'required',
            'useful_life' => 'required',
            'salvage_value' => 'required',
            'unit' => 'required'
        ]);

        \DB::beginTransaction();

        $fixed_assets_item = FixedAssetsItem::find($id);
        $fixed_assets_item->code = $request->input('code');
        $fixed_assets_item->name = $request->input('name');
        $fixed_assets_item->account_asset_id = $request->input('account_fixed_asset_id');
        $fixed_assets_item->useful_life = number_format_db($request->input('useful_life'));
        $fixed_assets_item->salvage_value = number_format_db($request->input('salvage_value'));
        $fixed_assets_item->unit = $request->input('unit');
        $fixed_assets_item->created_by = auth()->user()->id;
        $fixed_assets_item->updated_by = auth()->user()->id;
        $fixed_assets_item->save();

        timeline_publish('create.fixed.assets.item', 'Success update fixed assets item');
        \DB::commit();

        gritter_success('Success update fixed assets item');
        return redirect('master/fixed-assets-item/' . $fixed_assets_item->id);
    }

    public function _delete()
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
            $fixed_assets_item = FixedAssetsItem::find(\Input::get('id'));
            $fixed_assets_item->delete();

            timeline_publish('delete.item', 'Delete fixed assets item '. ['name' => $fixed_assets_item->name]);

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
            gritter_success('Delete Fixed Assets Item "' . $fixed_assets_item->name . '" Success');
        }

        return $response;
    }

    public function _getUsefulLife()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $account_fixed_asset = Coa::find(\Input::get('account_id'));
        return $account_fixed_asset->getUsefulLife();
    }

    public function _getAttribute()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $attribute = \Input::get('attribute');
        $account_fixed_asset = FixedAssetsItem::find(\Input::get('item_id'));
        if ($attribute == 'salvage_value' || $attribute == 'useful_life') {
            return number_format_quantity($account_fixed_asset->$attribute, 0);
        }

        return $account_fixed_asset->$attribute;
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.fixed.assets.item')) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $item = FixedAssetsItem::find($request->input('index'));

        if (!$item) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $item->disabled = $item->disabled == 0 ? 1 : 0;
        $item->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $item->disabled);

        return response()->json($response);
    }

    public function _list()
    {
        $items = FixedAssetsItem::active()->get();
        $list_item = [];
        foreach ($items as $item) {
            array_push($list_item, ['text' => $item->codeName, 'value' => $item->id]);
        }
        $response = array(
            'lists' => $list_item
        );
        return response()->json($response);
    }
}

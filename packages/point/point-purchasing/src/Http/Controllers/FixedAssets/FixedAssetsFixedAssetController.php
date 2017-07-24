<?php

namespace Point\PointPurchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\PointPurchasing\Helpers\AssetHelper;
use Point\PointPurchasing\Models\Asset;
use Point\PointPurchasing\Models\AssetsDetail;
use Point\PointPurchasing\Models\AssetRefer;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\CoaCategory;

class FixedAssetController extends Controller
{
    use ValidationTrait;

    protected $list_payment;
    protected $list_supplier;

    public function __construct()
    {
        $this->list_payment = AssetHelper::getAssetRefer([6]);
        $this->list_supplier = PersonHelper::getByType(['supplier']);
        \View::share(['list_payment' => $this->list_payment, 'list_supplier' => $this->list_supplier ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.fixed.asset');

        $view = view('point-purchasing::app.purchasing.point.fixed-asset.index');

        $view->list_assets = Asset::joinFormulir()->notArchived()->search(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->paginate(100);

        return $view;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.fixed.asset');

        $view = view('point-purchasing::app.purchasing.point.fixed-asset.create');

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
            'account_asset_id'=>'required',
            'form_date' => 'required',
            'purchase_date' => 'required',
            'name' => 'required',
        ]);

        formulir_is_allowed_to_create('create.point.purchasing.fixed.asset', date_format_db(\Input::get('form_date')), []);

        \DB::beginTransaction();

        $formulir = formulir_create($request, 'point-purchasing-assets');

        $assets = AssetHelper::create($formulir, $request, 0);

        timeline_publish('create.point.purchasing.fixed.asset', auth()->user()->name. ' has been success to add form asset ' . $formulir->form_number);

        \DB::commit();

        gritter_success('assets "'. $formulir->form_number .'" Success to add', 'false');

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
        access_is_allowed('read.point.purchasing.fixed.asset');

        $view = view('point-purchasing::app.purchasing.point.fixed-asset.show');

        $view->assets = Asset::find($id);

        if ($view->assets->formulir->form_number == null) {
            return redirect('purchasing/point/asset/'.$view->assets->id.'/archived');
        }

        $view->list_assets_archived = Asset::archived()->uniqueNumber()->where('archived', '=', $view->assets->formulir->form_number)->get();

        $view->revision = $view->list_assets_archived->count();

        return $view;
    }


    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.fixed.asset');

        $view = view('point-purchasing::app.purchasing.point.fixed-asset.archived');

        $view->assets_archived = Asset::find($id);

        $view->assets = Asset::notArchived()->where('form_number', '=', $view->assets_archived->formulir->archived)->first();

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
        access_is_allowed('update.point.purchasing.fixed.asset');

        $view = view('point-purchasing::app.purchasing.point.fixed-asset.edit');

        $view->assets = Asset::form($id)->first();

        $category = CoaCategory::where(strtolower('name'), 'fixed assets')->first();

        $view->list_coa = $category->coa()->get();

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
            'account_asset_id'=>'required',
            'form_date' => 'required',
            'purchase_date' => 'required',
            'name' => 'required',
        ]);

        $formulir_check = Formulir::find($id);

        formulir_is_allowed_to_update('update.point.purchasing.fixed.asset', $formulir_check->form_date, $formulir_check);

        \DB::beginTransaction();

        $formulir_old = formulir_archive($formulir_check->id, '');
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        AssetRefer::where('asset_id', $formulir_check->taskable_id)->update([ 'asset_id' => null ]);
        $assets = AssetHelper::create($formulir, $request, 1);

        timeline_publish('create.point.purchasing.fixed.asset', auth()->user()->name. ' has been success to update form asset ' . $formulir->form_number);
        gritter_success('assets "'. $formulir->form_number .'" Success to Update', 'false');
        
        \DB::commit();

        return redirect('purchasing/point/asset/'.$formulir->formulir_id);
    }

    public function _listAsset()
    {
        $name = 'fixed assets';

        $category = CoaCategory::where(strtolower('name'), $name)->first();
        $selected_coa = $category->coa()->get();
        $coa_array = [];
        
        foreach ($selected_coa as $coa) {
            array_push($coa_array, ['text'=>$coa->account, 'value'=>$coa->id]);
        }
        $response = array(
            'lists' => $coa_array
        );
        return response()->json($response);
    }

    /**
     * get item unit from ajax request
     * @return \Illuminate\Http\JsonResponse
     */
    public function _detail()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $item = AssetRefer::joinParents()->where('point_finance_payment_order_detail.id', \Input::get('item_id'))->first();

        $response = array(
            'date' => $item->form_date,
            'notes' => $item->notes_detail,
            'payment' => $item->amount,
        );

        return response()->json($response);
    }
}

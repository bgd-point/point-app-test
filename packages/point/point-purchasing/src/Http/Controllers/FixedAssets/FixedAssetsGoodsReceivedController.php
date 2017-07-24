<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsGoodsReceivedHelper;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;

class FixedAssetsGoodsReceivedController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.goods-received.index');
        $list_goods_received = FixedAssetsGoodsReceived::joinFormulir()->joinSupplier()->notArchived()->selectOriginal()->orderByStandard();
        $list_goods_received = FixedAssetsGoodsReceivedHelper::searchList($list_goods_received, \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_goods_received = $list_goods_received->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.goods-received.create-step-1');

        $view->list_purchase_include_expedition = FixedAssetsPurchaseOrder::includeExpedition()->paginate(100);
        $view->list_purchase_exclude_expedition = FixedAssetsPurchaseOrder::excludeExpedition()->paginate(100);

        return $view;
    }

    public function createStep2($purchase_order_id, $expedition_id = '')
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.goods-received.create-step-2');
        $view->reference_expedition_order = $expedition_id ? ExpeditionOrder::find($expedition_id) : '';
        $view->reference_purchase_order = FixedAssetsPurchaseOrder::find($purchase_order_id);
        $view->list_warehouse = Warehouse::all();
        $expedition_reference = '';
        if ($expedition_id) {
            $expedition_reference = ExpeditionOrderItem::where('point_expedition_order_id', $expedition_id)->get();
        }
        $view->expedition = $expedition_reference;
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
            'form_date' => 'required',
            'warehouse_id' => 'required',
        ]);
        
        $reference_purchase_order = $request->input('reference_purchase_order');
        $reference_purchase_id = $request->input('reference_purchase_order_id');
        $reference_purchase = $reference_purchase_order::find($reference_purchase_id);

        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.purchasing.goods.received.fixed.assets', date_format_db($request->input('form_date'), $request->input('time')), [$reference_purchase->formulir_id]);
        $formulir = FormulirHelper::create($request, 'point-purchasing-goods-received');
        $goods_received = FixedAssetsGoodsReceivedHelper::create($request, $formulir);
        timeline_publish('create.purchase.order', 'added new goods received '  . $goods_received->formulir->form_number);

        DB::commit();

        gritter_success('received success', false);
        return redirect('/purchasing/point/fixed-assets/goods-received/'.$goods_received->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.goods-received.show');
        $view->goods_received = FixedAssetsGoodsReceived::find($id);
        $view->reference = FormulirHelper::getLockedModel($view->goods_received->formulir_id);
        $view->list_goods_received_archived = FixedAssetsGoodsReceived::joinFormulir()->archived($view->goods_received->formulir->form_number)->get();
        $view->revision = $view->list_goods_received_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.goods-received.archived');
        $view->goods_received_archived = FixedAssetsGoodsReceived::find($id);
        $view->goods_received = FixedAssetsGoodsReceived::notArchived()->where('nomer', '=', $view->goods_received_archived->archived)->first();
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
        $goods_received = FixedAssetsGoodsReceived::find($id);
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.goods-received.edit');
        $view->goods_received = $goods_received;
        $view->reference_purchase_order = $goods_received->checkReference();
        $view->reference_expedition_order = $goods_received->checkReferenceExpedition($view->reference_purchase_order);
        $view->list_warehouse = Warehouse::all();

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
            'form_date' => 'required',
            'warehouse_id' => 'required',
            'edit_notes' => 'required',
        ]);

        if (number_format_db($request->input('is_cash')) > 0 && number_format_db($request->input('value_deliver')) > number_format_db($request->input('dp_amount'))) {
            $this->validate($request, [
                'approval_to' => 'required',
            ]);
        }
        
        DB::beginTransaction();

        $goods_received = FixedAssetsGoodsReceived::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.goods.received.fixed.assets', date_format_db($request->input('form_date')), $goods_received->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $goods_received->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $goods_received = FixedAssetsGoodsReceivedHelper::create($request, $formulir);
        timeline_publish('update.goods.received', 'update goods received '  . $goods_received->formulir->form_number);

        DB::commit();

        gritter_success('received success', false);
        return redirect('/purchasing/point/fixed-assets/goods-received/'.$goods_received->id);
    }
}

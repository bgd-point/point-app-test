<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointPurchasing\Helpers\GoodsReceivedHelper;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;

class GoodsReceivedController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.goods.received');

        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.index');
        $list_goods_received = GoodsReceived::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();
        $list_goods_received = GoodsReceivedHelper::searchList($list_goods_received, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_goods_received = $list_goods_received->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.purchasing.goods.received');

        $list_goods_received = GoodsReceived::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();
        $list_goods_received = GoodsReceivedHelper::searchList($list_goods_received, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-purchasing::app.purchasing.point.inventory.goods-received.index-pdf', ['list_goods_received' => $list_goods_received]);
        return $pdf->stream();
    }

    public function createStep1()
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.create-step-1');
        $view->list_person = PurchaseOrder::joinFormulir()->joinSupplier()->notArchived()->approvalApproved()->select('person.*')->get();

        return $view;
    }

    public function createStep2($supplier_id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.create-step-2');
        $view->list_purchase_include_expedition = PurchaseOrder::includeExpedition($supplier_id)->paginate(100);
        $view->list_purchase_exclude_expedition = PurchaseOrder::excludeExpedition($supplier_id);
        $view->purchase_order_exclude_expedition = ExpeditionOrder::joinFormulir()->approvalApproved()->notArchived()->where('done', 0)->whereIn('form_reference_id', $view->list_purchase_exclude_expedition['reference_id'])->selectOriginal()->get();

        return $view;
    }

    public function createStep3($purchase_order_id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.create-step-3');

        $purchase_order = PurchaseOrder::find($purchase_order_id);
        $view->list_expedition_order = ExpeditionOrder::joinFormulir()->approvalApproved()->notArchived()->where('done', 0)->where('form_reference_id', $purchase_order->formulir_id)->orderBy('group')->selectOriginal()->paginate(100);

        return $view;
    }

    public function createStep4($purchase_order_id, $group_expedition = '')
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.create-step-4');
        $view->reference_purchase_order = PurchaseOrder::find($purchase_order_id);
        $view->reference_expedition_order = $group_expedition ? ExpeditionOrder::joinFormulir()->notArchived()->approvalApproved()->where('done', 0)->where('group', $group_expedition)->where('form_reference_id', $view->reference_purchase_order->formulir_id)->selectOriginal()->first() : '';
        $view->list_item = $view->reference_expedition_order ? : $view->reference_purchase_order;
        $view->list_warehouse = Warehouse::all();
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

        for ($i=0; $i < count(\Input::get('item_quantity')); $i++) {
            if (! \Input::get('item_quantity')[$i] > 0) {
                gritter_error('Failed, quantity delivery must be more than zero', false);
                return redirect()->back();
            }
        }
        $reference_purchase_order = $request->input('reference_purchase_order');
        $reference_purchase_id = $request->input('reference_purchase_order_id');
        $reference_purchase = $reference_purchase_order::find($reference_purchase_id);

        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.purchasing.goods.received', date_format_db($request->input('form_date'), $request->input('time')), [$reference_purchase->formulir_id]);
        $formulir = FormulirHelper::create($request, 'point-purchasing-goods-received');
        $goods_received = GoodsReceivedHelper::create($request, $formulir);
        timeline_publish('create.point.purchasing.goods.received', 'added new goods received '  . $goods_received->formulir->form_number);

        DB::commit();

        gritter_success('received success', false);
        return redirect('/purchasing/point/goods-received/'.$goods_received->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.show');
        $view->goods_received = GoodsReceived::find($id);
        $view->reference = FormulirHelper::getLockedModel($view->goods_received->formulir_id);
        $view->list_goods_received_archived = GoodsReceived::joinFormulir()->archived($view->goods_received->formulir->form_number)->get();
        $view->revision = $view->list_goods_received_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.archived');
        $view->goods_received_archived = GoodsReceived::find($id);
        $view->goods_received = GoodsReceived::joinFormulir()->notArchived()->where('formulir.form_number', '=', $view->goods_received_archived->formulir->archived)->selectOriginal()->first();
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
        $goods_received = GoodsReceived::find($id);
        $view = view('point-purchasing::app.purchasing.point.inventory.goods-received.edit');
        $view->goods_received = $goods_received;
        $view->reference = $goods_received->checkReference();
        $view->reference_expedition_order = get_class($view->reference) == get_class(new ExpeditionOrder) ? $view->reference : null;
        $view->reference_purchase_order = get_class($view->reference) == get_class(new PurchaseOrder) ? $view->reference : $view->reference->reference();
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
        
        for ($i=0; $i < count(\Input::get('item_quantity')); $i++) {
            if (! \Input::get('item_quantity')[$i] > 0) {
                gritter_error('Failed, quantity delivery must be more than zero', false);
                return redirect()->back();
            }
        }
        
        DB::beginTransaction();

        $goods_received = GoodsReceived::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.goods.received', date_format_db($request->input('form_date')), $goods_received->formulir);
        GoodsReceivedHelper::undoneExpedition($goods_received);
        $formulir_old = FormulirHelper::archive($request->input(), $goods_received->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $goods_received = GoodsReceivedHelper::create($request, $formulir);
        timeline_publish('update.goods.received', 'update goods received '  . $goods_received->formulir->form_number);

        DB::commit();

        gritter_success('received success', false);
        return redirect('/purchasing/point/goods-received/'.$goods_received->id);
    }
}

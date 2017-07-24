<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsPurchaseOrderHelper;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsPurchaseRequisitionHelper;
use Point\PointPurchasing\Http\Requests\FixedAssetsRequest;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisition;

class FixedAssetsPurchaseOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.order.fixed.assets');
        
        $list_purchase_order = FixedAssetsPurchaseOrder::joinFormulir()->joinSupplier()->notArchived()->selectOriginal()->orderByStandard();
        $list_purchase_order = FixedAssetsPurchaseOrderHelper::searchList($list_purchase_order, \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.index');

        $view->list_purchase_order = $list_purchase_order->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.order.fixed.assets');
        
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.basic.create');
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::active()->get();
        $view->list_account  = Coa::getByCategory('Fixed Assets');
        return $view;
    }

    public function createStep1()
    {
        access_is_allowed('create.point.purchasing.order.fixed.assets');
        
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.create-step-1');
        $view->list_purchase_requisition = FixedAssetsPurchaseRequisitionHelper::availableToOrder();

        return $view;
    }

    public function createStep2($point_purchasing_requisition_id)
    {
        access_is_allowed('create.point.purchasing.order.fixed.assets');
        
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.create-step-2');
        $view->purchase_requisition = FixedAssetsPurchaseRequisition::find($point_purchasing_requisition_id);
        $view->list_supplier = PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FixedAssetsRequest $request)
    {
        $request['form_date'] = $request->input('required_date');

        DB::beginTransaction();

        $reference = null;
        if ($request->input('reference_type') != '') {
            $reference_type = $request->input('reference_type');
            $reference_id = $request->input('reference_id');
            $reference = $reference_type::find($reference_id)->formulir_id;
        }

        FormulirHelper::isAllowedToCreate('create.point.purchasing.order.fixed.assets', date_format_db($request->input('form_date'), $request->input('time')), $reference ? [$reference] : []);

        $formulir = FormulirHelper::create($request, 'point-purchasing-order');
        $purchase_order = FixedAssetsPurchaseOrderHelper::create($request, $formulir);
        timeline_publish('create.point.purchasing.order.fixed.assets', 'added new purchase order '  . $purchase_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/fixed-assets/purchase-order/'.$purchase_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.purchasing.order.fixed.assets');
        
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.show');
        $view->purchase_order = FixedAssetsPurchaseOrder::find($id);
        $view->reference = $view->purchase_order->checkHaveReference() ? : null;
        $view->list_purchase_order_archived = FixedAssetsPurchaseOrder::joinFormulir()->archived($view->purchase_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_purchase_order_archived->count();
        if (! $view->purchase_order->formulir->form_number) {
            return redirect(FixedAssetsPurchaseOrder::showUrl($id));
        }

        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.order.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.archived');
        $view->purchase_order_archived = FixedAssetsPurchaseOrder::find($id);
        $view->purchase_order = FixedAssetsPurchaseOrder::joinFormulir()->notArchived($view->purchase_order_archived->archived)->selectOriginal()->first();
        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.purchasing.order.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.edit');
        $view->purchase_order = FixedAssetsPurchaseOrder::find($id);
        $view->purchase_requisition = $view->purchase_order->checkHaveReference();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        return $view;
    }

    public function editBasic($id)
    {
        access_is_allowed('update.point.purchasing.order.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-order.basic.edit');
        $view->purchase_order = FixedAssetsPurchaseOrder::find($id);
        $view->list_account  = Coa::getByCategory('Fixed Assets');
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(FixedAssetsRequest $request, $id)
    {
        DB::beginTransaction();

        $purchase_order = FixedAssetsPurchaseOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.order.fixed.assets', date_format_db($request->input('form_date')), $purchase_order->formulir);
        ExpeditionOrderReference::where('expedition_reference_id', $purchase_order->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $purchase_order->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $purchase_order = FixedAssetsPurchaseOrderHelper::create($request, $formulir);
        timeline_publish('update.point.purchasing.order.fixed.assets', 'update purchase order '  . $purchase_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/fixed-assets/purchase-order/'.$purchase_order->id);
    }
}

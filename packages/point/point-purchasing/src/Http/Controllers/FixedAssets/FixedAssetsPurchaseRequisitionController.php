<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsPurchaseRequisitionHelper;
use Point\PointPurchasing\Http\Requests\FixedAssetsRequest;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisition;

class FixedAssetsPurchaseRequisitionController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.requisition.fixed.assets');

        $list_purchase_requisition = FixedAssetsPurchaseRequisition::joinFormulir()->joinSupplier()->notArchived()->selectOriginal()->orderByStandard();

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-requisition.index');
        $view->list_purchase_requisition = FixedAssetsPurchaseRequisitionHelper::searchList($list_purchase_requisition, \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_purchase_requisition = $view->list_purchase_requisition->paginate(100);
        
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.requisition.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-requisition.create');
        $view->list_supplier= PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->list_account = Coa::getByCategory('Fixed Assets');

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

        FormulirHelper::isAllowedToCreate('create.point.purchasing.requisition.fixed.assets', date_format_db($request['form_date']), []);
        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-requisition');
        $purchase_requisition = FixedAssetsPurchaseRequisitionHelper::create($request, $formulir);
        timeline_publish('create.purchase.requisition', 'added new purchase requisition '  . $purchase_requisition->formulir->form_number);

        DB::commit();

        gritter_success(trans('framework::framework/global.formulir.create.success'), 'false');
        return redirect('purchasing/point/fixed-assets/purchase-requisition/'.$purchase_requisition->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.purchasing.requisition.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-requisition.show');
        $purchase_requisition = FixedAssetsPurchaseRequisition::find($id);
        $view->purchase_requisition = $purchase_requisition;
        $view->list_purchase_requisition_archived = FixedAssetsPurchaseRequisition::joinFormulir()->archived($purchase_requisition->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_purchase_requisition_archived->count();

        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.requisition.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-requisition.archived');
        $view->purchase_requisition_archived = FixedAssetsPurchaseRequisition::find($id);
        $view->purchase_requisition = FixedAssetsPurchaseRequisition::joinFormulir()->notArchived($view->purchase_requisition_archived->archived)->selectOriginal()->first();
        
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
        access_is_allowed('update.point.purchasing.requisition.fixed.assets');


        $view = view('point-purchasing::app.purchasing.point.fixed-assets.purchase-requisition.edit');
        $purchase_requisition = FixedAssetsPurchaseRequisition::find($id);
        $view->purchase_requisition = $purchase_requisition;
        $view->list_supplier= PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->list_account = Coa::getByCategory('Fixed Assets');

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
        $request['form_date'] = $request->input('required_date');
        
        $purchase_requisition = FixedAssetsPurchaseRequisition::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.requisition.fixed.assets', date_format_db($request['form_date']), $purchase_requisition->formulir);
        $formulir_old = FormulirHelper::archive($request->input(), $purchase_requisition->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $purchase_requisition = FixedAssetsPurchaseRequisitionHelper::create($request, $formulir);
        timeline_publish('update.purchase.requisition', 'update purchase requisition '  . $purchase_requisition->formulir->form_number);

        DB::commit();

        gritter_success(trans('framework::framework/global.formulir.update.success'), 'false');
        return redirect('purchasing/point/fixed-assets/purchase-requisition/'.$purchase_requisition->id);
    }
}

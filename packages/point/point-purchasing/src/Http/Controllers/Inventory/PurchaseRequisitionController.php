<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemCategory;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointPurchasing\Helpers\PurchaseRequisitionHelper;
use Point\PointPurchasing\Http\Requests\PurchaseRequest;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisitionItem;

class PurchaseRequisitionController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.requisition');

        $list_purchase_requisition = PurchaseRequisition::joinFormulir()->joinEmployee()->notArchived()->selectOriginal();

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-requisition.index');
        $view->list_purchase_requisition = PurchaseRequisitionHelper::searchList($list_purchase_requisition, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_purchase_requisition = $view->list_purchase_requisition->paginate(100);

        $data_id = [];
        $view->data_id = $data_id;
        return $view;
    }

    public function ajaxDetailItem($id){
        access_is_allowed('read.point.purchasing.requisition');
        $list_purchase_order = PurchaseRequisitionItem::select('item.name as item_name','point_purchasing_requisition_item.quantity','point_purchasing_requisition_item.price','point_purchasing_requisition_item.point_purchasing_requisition_id')->joinAllocation()->joinItem()->joinPurchasingRequisition()->joinSupplier()->joinFormulir()->where('point_purchasing_requisition_item.point_purchasing_requisition_id', '=', $id)->get();
        return response()->json($list_purchase_order);
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.purchasing.requisition');
        $list_purchase_requisition = PurchaseRequisition::joinFormulir()->joinEmployee()->notArchived()->selectOriginal();
        $list_purchase_requisition = PurchaseRequisitionHelper::searchList($list_purchase_requisition, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-purchasing::app.purchasing.point.inventory.purchase-requisition.index-pdf', ['list_purchase_requisition' => $list_purchase_requisition]);
        
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.requisition');

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-requisition.create');
        $view->list_employee= PersonHelper::getByType(['employee']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->person_type = PersonHelper::getType('supplier');
        $view->list_group = PersonGroup::where('person_type_id', '=', $view->person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($view->person_type);
        $inventories_account = CoaCategory::where('name', 'Inventories')->first();
        $view->list_account_asset = $inventories_account->coa;
        $view->list_item_category = ItemCategory::active()->get();
        $view->list_item_unit = ItemUnit::groupBy('name')->get();
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PurchaseRequest $request)
    {
        $request['form_date'] = $request->input('required_date');

        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.purchasing.requisition', date_format_db($request['form_date']), []);
        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-requisition');
        $purchase_requisition = PurchaseRequisitionHelper::create($request, $formulir);
        timeline_publish('create.purchase.requisition', 'added new purchase requisition '  . $purchase_requisition->formulir->form_number);

        DB::commit();

        gritter_success(trans('framework::framework/global.formulir.create.success'), 'false');
        return redirect('purchasing/point/purchase-requisition/'.$purchase_requisition->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.purchasing.requisition');

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-requisition.show');
        $purchase_requisition = PurchaseRequisition::find($id);
        $view->purchase_requisition = $purchase_requisition;
        $view->list_purchase_requisition_archived = PurchaseRequisition::joinFormulir()->archived($purchase_requisition->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_purchase_requisition_archived->count();
        
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.requisition');

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-requisition.archived');
        $view->purchase_requisition_archived = PurchaseRequisition::find($id);
        $view->purchase_requisition = PurchaseRequisition::joinFormulir()->notArchived($view->purchase_requisition_archived->archived)->selectOriginal()->first();
        
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
        access_is_allowed('update.point.purchasing.requisition');


        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-requisition.edit');
        $purchase_requisition = PurchaseRequisition::find($id);
        $view->purchase_requisition = $purchase_requisition;
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->person_type = PersonHelper::getType('supplier');
        $view->list_group = PersonGroup::where('person_type_id', '=', $view->person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($view->person_type);
        $inventories_account = CoaCategory::where('name', 'Inventories')->first();
        $view->list_account_asset = $inventories_account->coa;
        $view->list_item_category = ItemCategory::active()->get();
        $view->list_item_unit = ItemUnit::groupBy('name')->get();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PurchaseRequest $request, $id)
    {
        DB::beginTransaction();

        $purchase_requisition = PurchaseRequisition::find($id);
        $request['form_date'] = $request->input('required_date');
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.requisition', date_format_db($request['form_date']), $purchase_requisition->formulir);
        $formulir_old = FormulirHelper::archive($request->input(), $purchase_requisition->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $purchase_requisition = PurchaseRequisitionHelper::create($request, $formulir);
        timeline_publish('update.purchase.requisition', 'update purchase requisition '  . $purchase_requisition->formulir->form_number);

        DB::commit();

        gritter_success(trans('framework::framework/global.formulir.update.success'), 'false');
        return redirect('purchasing/point/purchase-requisition/'.$purchase_requisition->id);
    }

    public function sendEmailRequisition(Request $request)
    {
        $id = app('request')->input('purchase_requisition_id');
        $purchase_requisition = PurchaseRequisition::joinSupplier()->where('point_purchasing_requisition.id', $id)->select('point_purchasing_requisition.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $purchase_requisition) {
            gritter_error('Failed, please select purchase requisition', 'false');
            return redirect()->back();
        }

        if (! $purchase_requisition->supplier->email) {
            gritter_error('Failed, please add email for supplier', 'false');
            return redirect()->back();
        }

        $data = array(
            'purchase_requisition' => $purchase_requisition,
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'PURCHASE REQUISITION '. $purchase_requisition->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $purchase_requisition, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-purchasing::emails.purchasing.point.external.purchase-requisition', $data, function ($message) use ($purchase_requisition, $warehouse, $data, $name) {
                $message->to($purchase_requisition->supplier->email)->subject($name);
                $pdf = \PDF::loadView('point-purchasing::emails.purchasing.point.external.purchase-requisition-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email purchase requisition', 'false');
        return redirect()->back();
    }
}

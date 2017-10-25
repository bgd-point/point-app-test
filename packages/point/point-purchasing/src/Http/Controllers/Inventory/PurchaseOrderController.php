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
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointPurchasing\Helpers\PurchaseOrderHelper;
use Point\PointPurchasing\Helpers\PurchaseRequisitionHelper;
use Point\PointPurchasing\Http\Requests\PurchaseRequest;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;

class PurchaseOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.order');
        
        $list_purchase_order = PurchaseOrder::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();
        $list_purchase_order = PurchaseOrderHelper::searchList($list_purchase_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.index');
        $view->list_purchase_order = $list_purchase_order->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.purchasing.order');
        $list_purchase_order = PurchaseOrder::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();
        $list_purchase_order = PurchaseOrderHelper::searchList($list_purchase_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-purchasing::app.purchasing.point.inventory.purchase-order.index-pdf', ['list_purchase_order' => $list_purchase_order]);
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.purchasing.order');
        
        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.basic.create');
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $person_type = PersonType::where('slug', 'supplier')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);
        return $view;
    }

    public function createStep1()
    {
        access_is_allowed('create.point.purchasing.order');
        
        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.create-step-1');
        $view->list_purchase_requisition = PurchaseRequisitionHelper::availableToOrder();
        return $view;
    }

    public function createStep2($point_purchasing_requisition_id)
    {
        access_is_allowed('create.point.purchasing.order');
        
        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.create-step-2');
        $view->purchase_requisition = PurchaseRequisition::find($point_purchasing_requisition_id);
        $view->list_supplier = PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->person_type = PersonHelper::getType('supplier');
        $view->list_group = PersonGroup::where('person_type_id', '=', $view->person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($view->person_type);
        
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

        $reference = null;
        if ($request->input('reference_type') != '') {
            $reference_type = $request->input('reference_type');
            $reference_id = $request->input('reference_id');
            $reference = $reference_type::find($reference_id)->formulir_id;
        }

        FormulirHelper::isAllowedToCreate('create.point.purchasing.order', date_format_db($request->input('form_date'), $request->input('time')), $reference ? [$reference] : []);

        $formulir = FormulirHelper::create($request, 'point-purchasing-order');
        $purchase_order = PurchaseOrderHelper::create($request, $formulir);
        timeline_publish('create.purchase.order', 'added new purchase order '  . $purchase_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/purchase-order/'.$purchase_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.purchasing.order');
        
        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.show');
        $view->purchase_order = PurchaseOrder::find($id);
        $view->reference = $view->purchase_order->checkHaveReference() ? : null;
        $view->list_purchase_order_archived = PurchaseOrder::joinFormulir()->archived($view->purchase_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_purchase_order_archived->count();
        if (! $view->purchase_order->formulir->form_number) {
            return redirect(PurchaseOrder::showUrl($id));
        }

        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.order');

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.archived');
        $view->purchase_order_archived = PurchaseOrder::find($id);
        $view->purchase_order = PurchaseOrder::joinFormulir()->notArchived($view->purchase_order_archived->archived)->selectOriginal()->first();
        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.purchasing.order');

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.edit');
        $view->purchase_order = PurchaseOrder::find($id);
        $view->purchase_requisition = $view->purchase_order->checkHaveReference();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->person_type = PersonHelper::getType('supplier');
        $view->list_group = PersonGroup::where('person_type_id', '=', $view->person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($view->person_type);
        return $view;
    }

    public function editBasic($id)
    {
        access_is_allowed('update.point.purchasing.order');

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.basic.edit');
        $view->purchase_order = PurchaseOrder::find($id);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $person_type = PersonType::where('slug', 'supplier')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);
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

        $purchase_order = PurchaseOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.order', date_format_db($request->input('form_date')), $purchase_order->formulir);
        $formulir_old = FormulirHelper::archive($request->input(), $purchase_order->formulir_id);
        ExpeditionOrderReference::where('expedition_reference_id', $purchase_order->formulir_id)->delete();
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $purchase_order = PurchaseOrderHelper::create($request, $formulir);
        timeline_publish('update.purchase.order', 'update purchase order '  . $purchase_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/purchase-order/'.$purchase_order->id);
    }

    public function sendEmailOrder(Request $request)
    {
        $id = app('request')->input('purchase_order_id');
        $purchase_order = PurchaseOrder::joinSupplier()->where('point_purchasing_order.id', $id)->select('point_purchasing_order.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $purchase_order) {
            gritter_error('Failed, please select purchase order', 'false');
            return redirect()->back();
        }

        if (! $purchase_order->supplier->email) {
            gritter_error('Failed, please add email for supplier', 'false');
            return redirect()->back();
        }

        $data = array(
            'purchase_order' => $purchase_order,
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'PURCHASE ORDER '. $purchase_order->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $purchase_order, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-purchasing::emails.purchasing.point.external.purchase-order', $data, function ($message) use ($purchase_order, $warehouse, $data, $name) {
                $message->to($purchase_order->supplier->email)->subject($name);
                $pdf = \PDF::loadView('point-purchasing::emails.purchasing.point.external.purchase-order-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email purchase order', 'false');
        return redirect()->back();
    }
}

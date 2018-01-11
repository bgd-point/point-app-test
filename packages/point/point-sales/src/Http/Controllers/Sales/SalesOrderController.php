<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointSales\Helpers\SalesOrderHelper;
use Point\PointSales\Helpers\SalesQuotationHelper;
use Point\PointSales\Models\Sales\SalesOrder;
use Point\PointSales\Models\Sales\SalesOrderItem;
use Point\PointSales\Models\Sales\SalesQuotation;

class SalesOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.order');
        
        $list_sales_order = SalesOrder::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_sales_order = SalesOrderHelper::searchList($list_sales_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view = view('point-sales::app.sales.point.sales.sales-order.index');
        $view->list_sales_order = $list_sales_order->paginate(100);
        
        $array_sales_order_id = [];
        $view->array_sales_order_id = $array_sales_order_id;
        return $view;
    }

    public function ajaxDetailItem(Request $request, $id)
    {
        access_is_allowed('read.point.sales.order');
        $list_sales_order = SalesOrderItem::select('item.name as item_name','point_sales_order_item.quantity','point_sales_order_item.unit','point_sales_order_item.price','point_sales_order_item.point_sales_order_id')->joinAllocation()->joinItem()->joinSalesOrder()->joinFormulir()->where('point_sales_order_item.point_sales_order_id', '=', $id)->get();
        return response()->json($list_sales_order);
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.order');
        $list_sales_order = SalesOrder::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_sales_order = SalesOrderHelper::searchList($list_sales_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.sales.sales-order.index-pdf', ['list_sales_order' => $list_sales_order]);
        
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        access_is_allowed('create.point.sales.order');
        
        $view = view('point-sales::app.sales.point.sales.sales-order.create-step-1');
        $view->list_sales_quotation = SalesQuotationHelper::availableToOrder();
        return $view;
    }

    public function createStep2($point_sales_quotation_id)
    {
        access_is_allowed('create.point.sales.order');
             
        $view = view('point-sales::app.sales.point.sales.sales-order.create-step-2');
        $view->sales_quotation = SalesQuotation::find($point_sales_quotation_id);
        $view->list_person = PersonHelper::getByType(['customer']);
        $view->list_user_approval = UserHelper::getAllUser();
        $person_type = PersonType::where('slug', 'customer')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);

        return $view;
    }

    public function create()
    {
        access_is_allowed('create.point.sales.order');

        $view = view('point-sales::app.sales.point.sales.sales-order.create');
        $view->list_employee= PersonHelper::getByType(['employee']);
        $view->list_customer= PersonHelper::getByType(['customer']);
        $view->list_allocation= Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $person_type = PersonType::where('slug', 'customer')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);

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
            'person_id' => 'required',
            'approval_to' => 'required',
        ]);

        DB::beginTransaction();

        $reference = null;
        if ($request->input('reference_type') != '') {
            $reference_type = $request->input('reference_type');
            $reference_id = $request->input('reference_id');
            $reference = $reference_type::find($reference_id)->formulir_id;
        }
        FormulirHelper::isAllowedToCreate('create.point.sales.order', date_format_db($request->input('form_date'), $request->input('time')), $reference ? [$reference] : []);

        $formulir = FormulirHelper::create($request->input(), 'point-sales-order');
        $sales_order = SalesOrderHelper::create($request, $formulir);
        timeline_publish('create.sales.order', 'added new sales order '  . $sales_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('sales/point/indirect/sales-order/'.$sales_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.sales.order');
        
        $view = view('point-sales::app.sales.point.sales.sales-order.show');
        $view->sales_order = SalesOrder::find($id);

        $formulir_lock = FormulirLock::where('locking_id', '=', $view->sales_order)->first();

        $view->noreference = false;

        if (! $formulir_lock == null) {
            $view->noreference = true;
            $formulir = Formulir::find($formulir_lock->locked_id);
            $view->reference = $formulir->formulirable_type::find($formulir->formulirable_id);
        }

        $view->list_sales_order_archived = SalesOrder::joinFormulir()->archived($view->sales_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_sales_order_archived->count();
        if (! $view->sales_order->formulir->form_number) {
            return redirect(SalesOrder::showUrl($id));
        }

        $view->list_referenced = FormulirLock::where('locked_id', '=', $view->sales_order->formulir_id)->get();

        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.sales.order');

        $view = view('point-sales::app.sales.point.sales.sales-order.archived');
        $view->sales_order_archived = SalesOrder::find($id);
        $view->sales_order = SalesOrder::joinFormulir()->notArchived($view->sales_order_archived->archived)->selectOriginal()->first();
        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.sales.order');

        $sales_order = SalesOrder::find($id);

        $view = view('point-sales::app.sales.point.sales.sales-order.edit');
        $view->sales_order = $sales_order;
        $view->sales_quotation = $sales_order->checkHaveReference();
        $view->list_person = PersonHelper::getByType(['customer']);
        $view->list_item = Item::get();
        $view->list_user_approval = UserHelper::getAllUser();
        $person_type = PersonType::where('slug', 'customer')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);

        return $view;
    }

    public function editNoref($id)
    {
        access_is_allowed('update.point.sales.order');

        $view = view('point-sales::app.sales.point.sales.sales-order.edit-no-ref');
        $view->sales_order = SalesOrder::find($id);
        $view->list_person = PersonHelper::getByType(['customer']);
        $view->list_item = Item::active()->get();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $person_type = PersonType::where('slug', 'customer')->first();
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
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'person_id' => 'required',
            'approval_to' => 'required',
            'edit_notes' => 'required'
        ]);

        DB::beginTransaction();

        $sales_order = SalesOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.sales.order', date_format_db($request->input('form_date')), $sales_order->formulir);
        $formulir_old = FormulirHelper::archive($request->input(), $sales_order->formulir_id);
        ExpeditionOrderReference::where('expedition_reference_id', $sales_order->formulir_id)->delete();
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $sales_order = SalesOrderHelper::create($request, $formulir);
        
        timeline_publish('update.sales.order', 'update sales order '  . $sales_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('sales/point/indirect/sales-order/'.$sales_order->id);
    }

    public function sendEmailOrder(Request $request)
    {
        $id = app('request')->input('sales_order_id');
        $sales_order = SalesOrder::joinPerson()->where('point_sales_order.id', $id)->select('point_sales_order.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $sales_order) {
            gritter_error('Failed, please select sales order', 'false');
            return redirect()->back();
        }

        if (! $sales_order->person->email) {
            gritter_error('Failed, please add email for customer', 'false');
            return redirect()->back();
        }

        $data = array(
            'sales_order' => $sales_order,
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'SALES ORDER '. $sales_order->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $sales_order, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.external.sales-order', $data, function ($message) use ($sales_order, $warehouse, $data, $name) {
                $message->to($sales_order->person->email)->subject($name);
                $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.sales-order-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email sales order', 'false');
        return redirect()->back();
    }
}

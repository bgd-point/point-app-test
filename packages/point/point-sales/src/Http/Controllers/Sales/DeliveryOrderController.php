<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointSales\Helpers\DeliveryOrderHelper;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\SalesOrder;

class DeliveryOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.delivery.order');

        $view = view('point-sales::app.sales.point.sales.delivery-order.index');
        $list_delivery_order = DeliveryOrder::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_delivery_order = DeliveryOrderHelper::searchList($list_delivery_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_delivery_order = $list_delivery_order->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.delivery.order');
        $list_delivery_order = DeliveryOrder::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_delivery_order = DeliveryOrderHelper::searchList($list_delivery_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.sales.delivery-order.index-pdf', ['list_delivery_order' => $list_delivery_order]);
        
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-sales::app.sales.point.sales.delivery-order.create-step-1');
        $view->list_sales_include_expedition = SalesOrder::includeExpedition()->paginate(100);
        $view->list_sales_exclude_expedition = SalesOrder::excludeExpedition()->paginate(100);

        return $view;
    }

    public function createStep2($sales_order_id, $expedition_id = '')
    {
        $view = view('point-sales::app.sales.point.sales.delivery-order.create-step-2');
        $view->reference_expedition_order = $expedition_id ? ExpeditionOrder::find($expedition_id) : '';
        $view->reference_sales_order = SalesOrder::find($sales_order_id);
        ;
        $view->list_warehouse = Warehouse::all();
        $view->list_user_approval = UserHelper::getAllUser();
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

        if (number_format_db($request->input('is_cash')) > 0 && (number_format_db($request->input('value_deliver')) + number_format_db($request->input('dp_amount'))) > number_format_db($request->input('dp_amount'))) {
            $this->validate($request, [
                'approval_to' => 'required',
            ]);
        }

        $reference_sales_type = $request->input('reference_sales_order');
        $reference_sales_id = $request->input('reference_sales_order_id');
        $reference_sales = $reference_sales_type::find($reference_sales_id);

        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.sales.delivery.order', date_format_db($request->input('form_date'), $request->input('time')), [$reference_sales->formulir_id]);
        $formulir = FormulirHelper::create($request->input(), 'point-sales-delivery-order');
        $delivery_order = DeliveryOrderHelper::create($request, $formulir);
        timeline_publish('create.sales.order', 'added new delivery order '  . $delivery_order->formulir->form_number);

        DB::commit();

        gritter_success('delivery order success', 'false');
        return redirect('/sales/point/indirect/delivery-order/'.$delivery_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-sales::app.sales.point.sales.delivery-order.show');
        $view->delivery_order = DeliveryOrder::find($id);
        $reference = FormulirHelper::getLockedModel($view->delivery_order->formulir_id);
        // convert to locking model from expedition order to sales order
        if (get_class($reference) == 'Point\PointExpedition\Models\ExpeditionOrder') {
            $reference = FormulirHelper::getLockedModel($reference->formulir_id);
        }
        $view->reference = $reference;
        $view->list_delivery_order_archived = DeliveryOrder::joinFormulir()->archived($view->delivery_order->formulir->form_number)->get();
        $view->revision = $view->list_delivery_order_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-sales::app.sales.point.sales.delivery-order.archived');
        $view->delivery_order_archived = DeliveryOrder::find($id);
        $view->delivery_order = DeliveryOrder::joinFormulir()->notArchived($view->delivery_order_archived->formulir->archived)->selectOriginal()->first();
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
        $delivery_order = DeliveryOrder::find($id);
        $view = view('point-sales::app.sales.point.sales.delivery-order.edit');
        $view->delivery_order = $delivery_order;
        $view->reference_sales_order = $delivery_order->checkReference();
        $view->reference_expedition_order = $delivery_order->checkReferenceExpedition($view->reference_sales_order);
        // $view->reference_sales_order = $view->reference_expedition ? '' : $view->reference;
        $view->list_warehouse = Warehouse::all();
        $view->list_user_approval = UserHelper::getAllUser();

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

        $delivery_order = DeliveryOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.sales.delivery.order', date_format_db($request->input('form_date')), $delivery_order->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $delivery_order->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $delivery_order = DeliveryOrderHelper::create($request, $formulir, $formulir_old->id);
        timeline_publish('update.delivery.order', 'update delivery order '  . $delivery_order->formulir->form_number);

        DB::commit();

        gritter_success('delivery order success', 'false');
        return redirect('/sales/point/indirect/delivery-order/'.$delivery_order->id);
    }
}

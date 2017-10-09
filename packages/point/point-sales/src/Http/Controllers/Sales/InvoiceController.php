<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Helpers\InvoiceHelper;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\Invoice;

class InvoiceController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.invoice');

        $view = view('point-sales::app.sales.point.sales.invoice.index');
        $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.invoice');
        $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.sales.invoice.index-pdf', ['list_invoice' => $list_invoice]);
        
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-sales::app.sales.point.sales.invoice.create-step-1');
        $view->list_delivery_order = DeliveryOrder::joinFormulir()
            ->availableToInvoiceGroupCustomer()
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep2($person_id)
    {
        $view = view('point-sales::app.sales.point.sales.invoice.create-step-2');
        $view->person_id = $person_id;
        $view->list_delivery_order = DeliveryOrder::joinFormulir()
            ->availableToInvoice($person_id)
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep3()
    {
        $view = view('point-sales::app.sales.point.sales.invoice.create-step-3');
        $array_delivery_order_id = explode(',', \Input::get('delivery_order_id'));
        $view->person = Person::find(\Input::get('person_id'));
        $view->list_delivery_order = DeliveryOrder::joinFormulir()
            ->whereIn('point_sales_delivery_order.formulir_id', $array_delivery_order_id)
            ->selectOriginal()
            ->get();

        $view->sales_order_tax = $view->list_delivery_order->first()->salesOrder->type_of_tax;
        $view->sales_order_discount = $view->list_delivery_order->first()->salesOrder->discount;
        $view->sales_order_expedition_fee = $view->list_delivery_order->first()->salesOrder->expedition_fee;

        $view->list_user_approval = UserHelper::getAllUser();
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
        $validator = \Validator::make($request->all(), [
            'form_date' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back();
        }

        DB::beginTransaction();

        $formulir_id = [];
        $references = [];
        $references_type = $request->input('reference_type');
        $references_id = $request->input('reference_id');
        for ($i=0; $i < count($references_type); $i++) {
            $reference = $references_type[$i]::find($references_id[$i]);
            array_push($references, $reference);
            array_push($formulir_id, $reference->formulir_id);
        }
        
        FormulirHelper::isAllowedToCreate('create.point.sales.invoice', date_format_db($request->input('form_date'), $request->input('time')), $formulir_id);

        $formulir = FormulirHelper::create($request->input(), 'point-sales-invoice');
        $invoice = InvoiceHelper::create($request, $formulir, $references);
        timeline_publish('create.invoice', 'added new invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('sales/point/indirect/invoice/'.$invoice->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-sales::app.sales.point.sales.invoice.show');
        $view->invoice = Invoice::find($id);
        $view->list_invoice_archived = Invoice::joinFormulir()->archived($view->invoice->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_invoice_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-sales::app.sales.point.sales.invoice.archived');
        $view->invoice_archived = Invoice::find($id);
        $view->invoice = Invoice::joinFormulir()->notArchived($view->invoice_archived->formulir->archived)->selectOriginal()->first();
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
        $view = view('point-sales::app.sales.point.sales.invoice.edit');
        $view->invoice = Invoice::find($id);
        $view->person = Person::find($view->invoice->person_id);
        $array_delivery_order_id = FormulirHelper::getLockedModelIds($view->invoice->formulir_id);
        $view->list_delivery_order = DeliveryOrder::joinFormulir()
            ->whereIn('point_sales_delivery_order.id', $array_delivery_order_id)
            ->selectOriginal()
            ->get();
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
        $validator = \Validator::make($request->all(), [
            'form_date' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('sales/point/indirect/invoice/create-step-1');
        }

        DB::beginTransaction();

        $references_type = $request->input('reference_type');
        $references_id = $request->input('reference_id');
        $formulir_id = [];
        $references = [];

        for ($i=0; $i < count($references_type); $i++) {
            $reference = $references_type[$i]::find($references_id[$i]);
            array_push($references, $reference);
            array_push($formulir_id, $reference->formulir_id);
        }

        $invoice = Invoice::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.sales.invoice', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = InvoiceHelper::create($request, $formulir, $references);
        timeline_publish('update.invoice', 'update invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', false);
        return redirect('sales/point/indirect/invoice/'.$invoice->id);
    }

    public function sendEmail(Request $request)
    {
        $id = app('request')->input('invoice_id');
        $invoice = Invoice::joinPerson()->where('point_sales_invoice.id', $id)->select('point_sales_invoice.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $invoice) {
            gritter_error('Failed, please select sales invoice', 'false');
            return redirect()->back();
        }

        if (! $invoice->person->email) {
            gritter_error('Failed, please add email for customer', 'false');
            return redirect()->back();
        }

        $data = array(
            'invoice' => $invoice, 
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'INVOICE '. $invoice->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $invoice, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.external.invoice', $data, function ($message) use ($invoice, $warehouse, $data, $name) {
                $message->to($invoice->person->email)->subject($name);
                $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.invoice-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email invoice', 'false');
        return redirect()->back();
    }

    public function exportPDF($id)
    {
        $invoice = Invoice::find($id);
        if ($invoice->approval_print_status == 0 || $invoice->approval_print_status == -1) {
            gritter_error('failed, please send request approval to administrator');
            return redirect('sales/point/indirect/invoice/'.$invoice->id);
        }

        $invoice->print_count = $invoice->print_count + 1;
        $invoice->approval_print_status = 0;
        $invoice->save();
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        $warehouse = $warehouse_id ? Warehouse::find($warehouse_id) : '';
        $data = array(
            'invoice' => $invoice, 
            'warehouse' => $warehouse
        );

        $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.invoice-pdf', $data);
        return $pdf->stream($invoice->formulir->form_number.'.pdf');
    }
}

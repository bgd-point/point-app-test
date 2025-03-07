<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\EmailHistory;
use Point\Framework\Models\Master\Gudang;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointPurchasing\Helpers\InvoiceHelper;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\Inventory\InvoiceItem;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;

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
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.index');
        $list_invoice = Invoice::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();

        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
     
        $array_invoice_id = [];
        $view->array_invoice_id = $array_invoice_id;
        return $view;
    }

    public function ajaxDetailItem($id)
    {
        $view = view('point-expedition::app.expedition.point.invoice.index');
        $list_purchase_order = InvoiceItem::select('item.name as item_name','point_purchasing_invoice_item.quantity','point_purchasing_invoice_item.price','point_purchasing_invoice_item.point_purchasing_invoice_id')->joinAllocation()->joinItem()->joinPurchasingInvoice()->joinSupplier()->joinFormulir()->where('point_purchasing_invoice_item.point_purchasing_invoice_id', '=', $id)->get();
        return response()->json($list_purchase_order);
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.purchasing.invoice');

        $list_invoice = Invoice::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-purchasing::app.purchasing.point.inventory.invoice.index-pdf', ['list_invoice' => $list_invoice]);
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.create-step-1');
        $view->list_goods_received = GoodsReceived::joinFormulir()
            ->availableToInvoiceGroupSupplier()
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep2($supplier_id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.create-step-2');
        $view->supplier_id = $supplier_id;
        $view->list_goods_received = GoodsReceived::joinFormulir()
            ->availableToInvoice($supplier_id)
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep3()
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.create-step-3');
        $array_goods_received_id = explode(',', \Input::get('goods_received_id'));
        $view->supplier = Person::find(\Input::get('supplier_id'));
        $view->list_goods_received = GoodsReceived::joinFormulir()
            ->whereIn('point_purchasing_goods_received.formulir_id', $array_goods_received_id)
            ->selectOriginal()
            ->get();
        $view->purchase_order_tax = $view->list_goods_received->first()->purchaseOrder->type_of_tax;
        $view->purchase_order_discount = $view->list_goods_received->first()->purchaseOrder->discount;
        $view->purchase_order_expedition_fee = $view->list_goods_received->first()->purchaseOrder->expedition_fee;

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
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back();
        }

        DB::beginTransaction();

        $formulir_id = [];
        $references = [];
        $references_id = $request->input('reference_id');
        $references_type = $request->input('reference_type');
        for ($i=0; $i < count($references_type); $i++) {
            $reference = $references_type[$i]::find($references_id[$i]);
            array_push($references, $reference);
            array_push($formulir_id, $reference->formulir_id);
        }

        FormulirHelper::isAllowedToCreate('create.point.purchasing.invoice', date_format_db($request->input('form_date'), $request->input('time')), $formulir_id);

        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-invoice');
        $invoice = InvoiceHelper::create($request, $formulir, $references);
        timeline_publish('create.invoice', 'added new invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/invoice/'.$invoice->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.show');
        $view->invoice = Invoice::find($id);
        $view->list_invoice_archived = Invoice::joinFormulir()->archived($view->invoice->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_invoice_archived->count();
        $view->list_referenced = FormulirLock::where('locked_id', '=', $view->invoice->formulir_id)->where('locked', true)->get();
        $view->list_reference = FormulirLock::where('locking_id', '=', $view->invoice->formulir_id)->where('locked', true)->get();
        $view->email_history = EmailHistory::where('formulir_id', $view->invoice->formulir_id)->get();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.archived');
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
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.edit');
        $view->invoice = Invoice::find($id);
        $view->invoice->tax_percentage = $view->invoice->tax / $view->invoice->tax_base * 100;
        $view->supplier = Person::find($view->invoice->supplier_id);
        $array_goods_received_id = FormulirHelper::getLockedModelIds($view->invoice->formulir_id);
        $view->list_goods_received = GoodsReceived::joinFormulir()
            ->whereIn('point_purchasing_goods_received.id', $array_goods_received_id)
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
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('purchasing/point/invoice/create-step-1');
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
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.invoice', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = InvoiceHelper::create($request, $formulir, $references);
        timeline_publish('update.invoice', 'update invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/invoice/'.$invoice->id);
    }

    public function sendEmail(Request $request)
    {
        $id = app('request')->input('invoice_id');
        $invoice = Invoice::joinSupplier()->where('point_purchasing_invoice.id', $id)->select('point_purchasing_invoice.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        } else {
            $warehouse = Warehouse::where('id', '>', 0)->first();
        }

        if (! $invoice) {
            gritter_error('Failed, please select purchasing invoice', 'false');
            return redirect()->back();
        }

        if (! $invoice->supplier->email) {
            gritter_error('Failed, please add email for supplier', 'false');
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
            \Mail::send('point-purchasing::emails.purchasing.point.external.invoice', $data, function ($message) use ($invoice, $warehouse, $data, $name) {
                $message->to($invoice->supplier->email)->subject($name);
                $pdf = \PDF::loadView('point-purchasing::emails.purchasing.point.external.invoice-standard-pdf', $data)->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email invoice', 'false');

        $email_history = new EmailHistory;
        $email_history->sender = auth()->id();
        $email_history->recipient = $invoice->supplier_id;
        $email_history->recipient_email = $invoice->supplier->email;
        $email_history->formulir_id = $invoice->formulir_id;
        $email_history->sent_at = \Carbon\Carbon::now()->toDateTimeString();
        $email_history->save();
        
        return redirect()->back();
    }

    public function exportPDF($id)
    {
        $invoice = Invoice::find($id);
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }
        $data = array(
            'invoice' => $invoice,
            'warehouse' => $warehouse
        );

        $pdf = \PDF::loadView('point-purchasing::emails.purchasing.point.external.invoice-standard-pdf', $data)->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        return $pdf->stream($invoice->formulir->form_number.'.pdf');
    }

    public function printBarcode($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.print-barcode');
        $view->invoice = Invoice::find($id);
        
        return $view;
    }
}

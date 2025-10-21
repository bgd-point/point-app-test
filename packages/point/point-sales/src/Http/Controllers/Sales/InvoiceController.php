<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\AccountPayableAndReceivableDetail;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\EmailHistory;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Helpers\InvoiceHelper;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\InvoiceItem;
use Point\PointSales\Models\Sales\Retur;
use Point\PointSales\Models\Sales\ReturItem;

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
        $array_invoice_id = [];
        $view->array_invoice_id = $array_invoice_id;
        return $view;
    }

    public function ajaxDetailItem(Request $request, $id)
    {
        access_is_allowed('read.point.sales.invoice');
        $list_invoice = InvoiceItem::select('item.name as item_name','point_sales_invoice_item.quantity','point_sales_invoice_item.unit','point_sales_invoice_item.price','point_sales_invoice_item.point_sales_invoice_id')->joinItem()->joinInvoice()->joinFormulir()->where('point_sales_invoice_item.point_sales_invoice_id', '=', $id)->get();
        return response()->json($list_invoice);
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.invoice');
        $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.sales.invoice.index-pdf', ['list_invoice' => $list_invoice])->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        
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
        $view->list_referenced = FormulirLock::where('locked_id', '=', $view->invoice->formulir_id)->where('locked', true)->get();
        $view->email_history = EmailHistory::where('formulir_id', $view->invoice->formulir_id)->get();
        $view->returs = Retur::joinFormulir()->where('formulir.form_status', '!=', -1)->where('point_sales_invoice_id', $id)->select('point_sales_retur.*')->get();
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
        $view->invoice->tax_percentage = $view->invoice->tax / $view->invoice->tax_base * 100;
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
                $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.invoice-pdf', $data)->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email invoice', 'false');

        $email_history = new EmailHistory;
        $email_history->sender = auth()->id();
        $email_history->recipient = $invoice->person_id;
        $email_history->recipient_email = $invoice->person->email;
        $email_history->formulir_id = $invoice->formulir_id;
        $email_history->sent_at = \Carbon\Carbon::now()->toDateTimeString();
        $email_history->save();
        
        return redirect()->back();
    }

    public function exportPDF($id)
    {
        $invoice = Invoice::find($id);
        if ($invoice->approval_print_status < 1) {
            gritter_error('failed, please send request approval to administrator');
            return redirect('sales/point/indirect/invoice/'.$invoice->id);
        }

        $invoice->print_count += 1;
        $invoice->approval_print_status = 0;
        $invoice->save();
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        $warehouse = $warehouse_id ? Warehouse::find($warehouse_id) : '';
        $data = array(
            'invoice' => $invoice,
            'warehouse' => $warehouse
        );

        $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.invoice-pdf', $data)->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        return $pdf->stream($invoice->formulir->form_number.'.pdf');
    }

    public function retur($id)
    {
        $view = view('point-sales::app.sales.point.sales.invoice.retur');
        $view->invoice = Invoice::find($id);
        return $view;
    }

    public function deleteRetur(Request $request, $id, $returId)
    {
        $retur = Retur::findOrFail($returId);
        $formulir_id = $retur->formulir_id;
        $permission_slug = 'create.point.sales.invoice';

        DB::beginTransaction();

        try {
            FormulirHelper::cancel($permission_slug, $formulir_id);
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        DB::commit();

        gritter_success('delete retur success', false);
        return redirect('sales/point/indirect/invoice/'.$retur->point_sales_invoice_id);
    }

    public function storeRetur(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'form_date' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back();
        }

        DB::beginTransaction();

        $invoice = Invoice::findOrFail($id);

        $formulir = FormulirHelper::create($request->input(), 'point-sales-return');

        $retur = new Retur;
        $retur->formulir_id = $formulir->id;
        $retur->person_id = $invoice->person_id;
        $retur->point_sales_invoice_id = $invoice->id;
        $retur->save();

        $subtotal = 0;
        $amount = 0;
        for ($i=0 ; $i < count($request->input('item_id')) ; $i++) {
            $amount = 0;
            if ($request->input('item_quantity')[$i] > 0) {
                $invoiceItem = InvoiceItem::where('item_id', $request->input('item_id')[$i])->where('point_sales_invoice_id', $invoice->id)->first();
                $retur_item = new ReturItem;
                $retur_item->point_sales_retur_id = $retur->id;
                $retur_item->item_id = $request->input('item_id')[$i];
                $retur_item->quantity = number_format_db($request->input('item_quantity')[$i]);
                $retur_item->price = $invoiceItem->price;
                $retur_item->discount = $invoiceItem->discount;
                $retur_item->unit = $invoiceItem->unit;
                $retur_item->allocation_id = $invoiceItem->allocation_id;
                $retur_item->converter = 1;
                $retur_item->save();

                $amount = ($retur_item->quantity * $retur_item->price) - ($retur_item->quantity * $retur_item->price/100 * $retur_item->discount);
                AllocationHelper::save($retur->formulir_id, $retur_item->allocation_id, $amount, $formulir->notes);
            }

            $subtotal += $amount;
        }

        $formulir->approval_status = 1;
        $formulir->save();

        $discount = 0;
        $tax_base = 0;
        $tax = 0;
        if ($invoice->tax > 0) {
            $tax = $subtotal / $invoice->subtotal * $invoice->tax;
        }
        $total = $subtotal + $tax;

        $retur->subtotal = $subtotal;
        $retur->tax = $tax;
        $retur->total = $total;
        $retur->save();

        $formDeliveryOrder = FormulirLock::where('locking_id', $invoice->formulir_id)->first()->lockedForm;
        $deliveryOrder = DeliveryOrder::findOrFail($formDeliveryOrder->formulirable_id);

        $cost_of_sales = 0;
        foreach ($invoice->items as $invoice_detail) {
            $warehouse_id = $deliveryOrder->warehouse_id;

            $retur_item = ReturItem::where('item_id', $invoice_detail->item_id)
                ->where('point_sales_retur_id', $retur->id)
                ->where('price', $invoice_detail->price)
                ->first();

            if ($retur_item) {
                // insert new inventory
                $inventory = new Inventory();
                $inventory->formulir_id = $formulir->id;
                $inventory->item_id = $retur_item->item_id;
                $inventory->quantity = $retur_item->quantity * $retur_item->converter;
                $inventory->price = InventoryHelper::getCostOfSales(date('Y-m-d H:i:s'), $retur_item->id, $warehouse_id);
                $inventory->form_date = date('Y-m-d H:i:s');
                $inventory->warehouse_id = $warehouse_id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();

                $cost = InventoryHelper::getCostOfSales(\Carbon::now(), $inventory->item_id, $inventory->warehouse_id) * abs($inventory->quantity);
                $cost_of_sales += $cost;

                $journal = new Journal;
                $journal->form_date = $retur->formulir->form_date;
                $journal->coa_id = $inventory->item->account_asset_id;
                $journal->description = 'invoice "' . $inventory->item->codeName.'"';
                $journal->debit = $cost;
                $journal->form_journal_id = $retur->formulir_id;
                $journal->form_reference_id = $invoice->formulir_id;
                $journal->subledger_id = $inventory->item_id;
                $journal->subledger_type = get_class($inventory->item);
                $journal->save();
            }
        }

        // Journal tax exclude and non-tax
        if ($invoice->type_of_tax == 'exclude' || $invoice->type_of_tax == 'non') {
            $data = array(
                'value_of_account_receivable' => $total * -1,
                'value_of_income_tax_payable' => $tax * -1,
                'value_of_sale_of_goods' => $subtotal * -1,
                'value_of_cost_of_sales' => $cost_of_sales * -1,
                'value_of_discount' => $discount,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            self::journalRetur($data);
        }

        // Journal tax include
        if ($request->input('type_of_tax') == 'include') {
            $data = array(
                'value_of_account_receivable' => $total * -1,
                'value_of_income_tax_payable' => $tax * -1,
                'value_of_sale_of_goods' => $tax_base * -1,
                'value_of_cost_of_sales' => $cost_of_sales * -1,
                'value_of_discount' => $discount,
                'formulir' => $formulir,
                'invoice' => $invoice
            );
            self::journalRetur($data);
        }

//        $invoice = Invoice::find($retur->point_sales_invoice_id);
//        $apr = AccountPayableAndReceivable::where('formulir_reference_id', $invoice->formulir_id)->first();
//
//        $account_payable_and_receivable_detail = new AccountPayableAndReceivableDetail;
//        $account_payable_and_receivable_detail->account_payable_and_receivable_id = $apr->id;
//        $account_payable_and_receivable_detail->formulir_reference_id = $retur->formulir->id;
//        $account_payable_and_receivable_detail->amount = $retur->total;
//        $account_payable_and_receivable_detail->form_date = $retur->formulir->form_date;
//        $account_payable_and_receivable_detail->notes = 'RETUR';
//        $account_payable_and_receivable_detail->save();

        JournalHelper::checkJournalBalance($invoice->formulir_id);

        timeline_publish('create.retur', 'added retur '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('retur form success', false);
        return redirect('sales/point/indirect/invoice/'.$id);
    }

    public static function journalRetur($data)
    {
        // 1. Journal Account Receivable
        $account_receivable = JournalHelper::getAccount('point sales indirect', 'account receivable');
        $position = JournalHelper::position($account_receivable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receivable;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_receivable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 2. Journal Income Tax  Payable
        if ($data['invoice']->tax != 0) {
            $income_tax_receivable = JournalHelper::getAccount('point sales indirect', 'income tax payable');
            $position = JournalHelper::position($income_tax_receivable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receivable;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_payable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        // 3. Journal Sales Of Goods
        $sales_of_goods = JournalHelper::getAccount('point sales indirect', 'sale of goods');
        $position = JournalHelper::position($sales_of_goods);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $sales_of_goods;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_sale_of_goods'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 4. Journal Sales Discount
        if ($data['invoice']->discount > 0) {
            $sales_discount = JournalHelper::getAccount('point sales indirect', 'sales discount');
            $position = JournalHelper::position($sales_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $sales_discount;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        $cost_of_sales_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $cost_of_sales_account;
        $journal->description = 'invoice indirect sales "' . $data['formulir']->form_number.'"';
        $journal->debit = $data['value_of_cost_of_sales'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }

    public static function journal($data)
    {
        // 1. Journal Account Receivable
        $account_receivable = JournalHelper::getAccount('point sales indirect', 'account receivable');
        $position = JournalHelper::position($account_receivable);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $account_receivable;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_account_receivable'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 2. Journal Income Tax  Payable
        if ($data['invoice']->tax != 0) {
            $income_tax_receivable = JournalHelper::getAccount('point sales indirect', 'income tax payable');
            $position = JournalHelper::position($income_tax_receivable);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $income_tax_receivable;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_income_tax_payable'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        // 3. Journal Sales Of Goods
        $sales_of_goods = JournalHelper::getAccount('point sales indirect', 'sale of goods');
        $position = JournalHelper::position($sales_of_goods);
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $sales_of_goods;
        $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
        $journal->$position = $data['value_of_sale_of_goods'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id = $data['invoice']->person_id;
        $journal->subledger_type = get_class($data['invoice']->person);
        $journal->save();

        // 4. Journal Sales Discount
        if ($data['invoice']->discount > 0) {
            $sales_discount = JournalHelper::getAccount('point sales indirect', 'sales discount');
            $position = JournalHelper::position($sales_discount);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $sales_discount;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_discount'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        // 5. Journal Expedition Cost
        if ($data['invoice']->expedition_fee > 0) {
            $cost_of_sales = JournalHelper::getAccount('point sales indirect', 'expedition income');
            $position = JournalHelper::position($cost_of_sales);
            $journal = new Journal;
            $journal->form_date = $data['formulir']->form_date;
            $journal->coa_id = $cost_of_sales;
            $journal->description = 'invoice indirect sales [' . $data['formulir']->form_number.']';
            $journal->$position = $data['value_of_expedition_income'];
            $journal->form_journal_id = $data['formulir']->id;
            $journal->form_reference_id;
            $journal->subledger_id = $data['invoice']->person_id;
            $journal->subledger_type = get_class($data['invoice']->person);
            $journal->save();
        }

        $cost_of_sales_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
        $journal = new Journal;
        $journal->form_date = $data['formulir']->form_date;
        $journal->coa_id = $cost_of_sales_account;
        $journal->description = 'invoice indirect sales "' . $data['formulir']->form_number.'"';
        $journal->debit = $data['value_of_cost_of_sales'];
        $journal->form_journal_id = $data['formulir']->id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }
}

<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Gudang;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Helpers\InvoiceHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\Invoice;
use Point\PointExpedition\Models\InvoiceItem;

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
        $view = view('point-expedition::app.expedition.point.invoice.index');
        $list_invoice = Invoice::joinFormulir()->notArchived()->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.expedition.invoice');

        $list_invoice = Invoice::joinFormulir()->notArchived()->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-expedition::app.expedition.point.invoice.index-pdf', ['list_invoice' => $list_invoice])->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-expedition::app.expedition.point.invoice.create-step-1');
        $view->list_expedition_order = ExpeditionOrder::joinFormulir()
            ->availableToInvoiceGroupExpedition()// group by expedition
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep2($expedition_id)
    {
        $view = view('point-expedition::app.expedition.point.invoice.create-step-2');
        $view->expedition_id = $expedition_id;
        $expedition_collection = ExpeditionOrder::joinFormulir()
            ->where('expedition_id', $expedition_id)
            ->open()
            ->notArchived()
            ->selectOriginal()
            ->get();
        $list_invoice_sales = [];
        $list_invoice_purchase = [];

        foreach ($expedition_collection as $expedition_order) {
            if (FormulirHelper::getLocked($expedition_order->formulir_id)->formulirable_type == "Point\PointSales\Models\Sales\SalesOrder") {
                array_push($list_invoice_sales, $expedition_order);
            } else {
                array_push($list_invoice_purchase, $expedition_order);
            }
        }
        $view->list_invoice_purchase = $list_invoice_purchase;
        $view->list_invoice_sales = $list_invoice_sales;

        return $view;
    }

    public function createStep3()
    {
        $view = view('point-expedition::app.expedition.point.invoice.create-step-3');
        $array_expedition_order_id = \Input::get('expedition_order_id');

        if (!$array_expedition_order_id) {
            gritter_error('Failed, please select expedition order');
            return back();
        }

        $view->expedition = Person::find(\Input::get('expedition_id'));
        $view->list_expedition_order_invoice = ExpeditionOrder::joinFormulir()
            ->whereIn('point_expedition_order.formulir_id', $array_expedition_order_id)
            ->selectOriginal()
            ->get();
        
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
        ]);

        DB::beginTransaction();

        $references_type = $request->input('reference_type_expedition');
        $references_id = $request->input('reference_id_expedition');
        $formulir_id = [];
        $references = [];

        for ($i = 0; $i < count($references_type); $i++) {
            $reference = $references_type[$i]::find($references_id[$i]);
            array_push($references, $reference);
            array_push($formulir_id, $reference->formulir_id);
        }

        FormulirHelper::isAllowedToCreate('create.point.expedition.invoice',
            date_format_db($request->input('form_date'), $request->input('time')), $formulir_id);

        $formulir = FormulirHelper::create($request->input(), 'point-expedition-invoice');
        $invoice = InvoiceHelper::create($request, $formulir, $references);
        timeline_publish('create.invoice', 'added new invoice ' . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success');
        return redirect('expedition/point/invoice/' . $invoice->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-expedition::app.expedition.point.invoice.show');
        $view->invoice = Invoice::find($id);
        // $view->reference = FormulirHelper::getLockedModel($view->invoice->formulir_id);
        $view->list_invoice_archived = Invoice::joinFormulir()->archived($view->invoice->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_invoice_archived->count();

        // $view->list_referenced = FormulirLock::where('locked_id', '=', $view->invoice->formulir_id)->where('locked', true)->get();

        return $view;
    }

    public function archived($id)
    {
        $view = view('point-expedition::app.expedition.point.invoice.archived');
        $view->invoice_archived = Invoice::find($id);
        $view->invoice = Invoice::joinFormulir()->notArchived($view->invoice_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoice = Invoice::find($id);
        $formulir_locks = FormulirLock::where('locking_id', $invoice->formulir_id)->get();
        $view = view('point-expedition::app.expedition.point.invoice.edit');
        $array_expedition_order_id = FormulirHelper::getLockedModelIds($invoice->formulir_id);
        $view->list_expedition_order = ExpeditionOrder::joinFormulir()
            ->whereIn('point_expedition_order.id', $array_expedition_order_id)
            ->selectOriginal()
            ->get();
        $view->list_invoice_item = InvoiceItem::joinItem()->where('point_expedition_invoice_id', $invoice->id)->get();
        $view->expedition = Person::find($invoice->expedition_id);
        $view->invoice = $invoice;
        $view->invoice->tax_percentage = $view->invoice->tax / $view->invoice->tax_base * 100;
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'edit_notes' => 'required',
        ]);

        DB::beginTransaction();

        $references_type = $request->input('reference_type');
        $references_id = $request->input('reference_id');
        $formulir_id = [];
        $references = [];

        for ($i = 0; $i < count($references_type); $i++) {
            $reference = $references_type[$i]::find($references_id[$i]);
            array_push($references, $reference);
            array_push($formulir_id, $reference->formulir_id);
        }

        $invoice = Invoice::find($id);
        access_is_allowed('update.point.expedition.invoice',
            date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = InvoiceHelper::create($request, $formulir, $references);
        timeline_publish('update.invoice', 'update invoice ' . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success');
        return redirect('expedition/point/invoice/' . $invoice->id);
    }

    public function sendEmail(Request $request)
    {
        $id = app('request')->input('invoice_id');
        $invoice = Invoice::joinExpedition()->where('point_expedition_invoice.id', $id)->select('point_expedition_invoice.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $invoice) {
            gritter_error('Failed, please select expedition invoice', 'false');
            return redirect()->back();
        }

        if (! $invoice->expedition->email) {
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
            \Mail::send('point-expedition::emails.expedition.point.external.invoice', $data, function ($message) use ($invoice, $warehouse, $data, $name) {
                $message->to($invoice->expedition->email)->subject($name);
                $pdf = \PDF::loadView('point-expedition::emails.expedition.point.external.invoice-pdf', $data)->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
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
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }
        $data = array(
            'invoice' => $invoice,
            'warehouse' => $warehouse
        );

        $pdf = \PDF::loadView('point-expedition::emails.expedition.point.external.invoice-pdf', $data)->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        return $pdf->download($invoice->formulir->form_number.'.pdf');
    }
}

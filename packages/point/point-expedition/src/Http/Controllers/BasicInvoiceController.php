<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Gudang;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Helpers\InvoiceHelper;
use Point\PointExpedition\Models\Invoice;

class BasicInvoiceController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        $view = view('point-expedition::app.expedition.point.invoice.basic.index');
        $list_invoice = Invoice::joinFormulir()->notArchived()->selectOriginal()->orderByStandard();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('date_from'), \Input::get('date_to'),
            \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }

    public function create()
    {
        $view = view('point-expedition::app.expedition.point.invoice.basic.create');
        $person_type = PersonHelper::getType('expedition');
        $view->list_expedition = PersonHelper::getByType(['expedition']);
        $view->list_item = Item::get();
        $view->person_type = $person_type;
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);

        return $view;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'expedition_id' => 'required',
            'total' => 'required',
        ]);

        if ($request->input('total') < 1) {
            gritter_error('Failed, total must not be zero ');
            return back();
        }
        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.expedition.invoice',
            date_format_db($request->input('form_date'), $request->input('time')), []);

        $formulir = FormulirHelper::create($request->input(), 'point-expedition-invoice');
        $invoice = InvoiceHelper::storeBasicInvoice($request, $formulir);
        timeline_publish('create.invoice', 'added new invoice ' . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success');
        return redirect('expedition/point/invoice/basic/' . $invoice->id);
    }

    public function show($id)
    {
        $view = view('point-expedition::app.expedition.point.invoice.basic.show');
        $view->invoice = Invoice::find($id);
        $view->list_invoice_archived = Invoice::joinFormulir()->archived($view->invoice->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_invoice_archived->count();

        return $view;
    }

    public function archived($id)
    {
        $view = view('point-expedition::app.expedition.point.invoice.basic/archived');
        $view->invoice_archived = Invoice::find($id);
        $view->invoice = Invoice::joinFormulir()->notArchived($view->invoice_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    public function edit($id)
    {
        $view = view('point-expedition::app.expedition.point.invoice.basic.edit');
        $view->invoice = Invoice::find($id);
        return $view;
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'expedition_id' => 'required',
            'total' => 'required',
            'edit_notes' => 'required',
        ]);

        if ($request->input('total') < 1) {
            gritter_error('Failed, total must not be zero ');
            return back();
        }

        DB::beginTransaction();

        $invoice = Invoice::find($request->input('invoice_id'));
        access_is_allowed('update.point.expedition.invoice',
            date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);
        
        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = InvoiceHelper::storeBasicInvoice($request, $formulir);
        timeline_publish('update.invoice', 'update invoice ' . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success');
        return redirect('expedition/point/invoice/basic/' . $invoice->id);
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
            gritter_error('Failed, please add email for expedition', 'false');
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
            \Mail::send('point-expedition::emails.expedition.point.external.basic-invoice', $data, function ($message) use ($invoice, $warehouse, $data, $name) {
                $message->to($invoice->expedition->email)->subject($name);
                $pdf = \PDF::loadView('point-expedition::emails.expedition.point.external.basic-invoice-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email invoice', 'false');
        return redirect()->back();
    }
}

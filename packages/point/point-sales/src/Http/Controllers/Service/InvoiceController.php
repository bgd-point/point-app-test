<?php

namespace Point\PointSales\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Helpers\ServiceInvoiceHelper;
use Point\PointSales\Http\Requests\ServiceInvoiceRequest;
use Point\PointSales\Models\Service\Invoice;

class InvoiceController extends Controller
{
    use ValidationTrait;
    
    public function index()
    {
        access_is_allowed('read.point.sales.service.invoice');

        $view = view('point-sales::app.sales.point.service.invoice.index');
        $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.service.invoice');
        $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.service.invoice.index-pdf', ['list_invoice' => $list_invoice]);
        
        return $pdf->stream();
    }

    public function create()
    {
        $view = view('point-sales::app.sales.point.service.invoice.create');
        $view->list_person = PersonHelper::getByType(['customer']);
        $view->list_item = Item::active()->get();
        $view->list_allocation = Allocation::active()->get();
        $person_type = PersonType::where('slug', 'customer')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);
        
        return $view;
    }

    //store created sales invoice basic feature
    public function store(ServiceInvoiceRequest $request)
    {
        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.sales.service.invoice', date_format_db($request->input('form_date'), $request->input('time')), []);

        $formulir = FormulirHelper::create($request->input(), 'point-sales-service-invoice');
        $invoice = ServiceInvoiceHelper::create($request, $formulir);
        timeline_publish('create.invoice', 'added new invoice service '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('sales/point/service/invoice/'.$invoice->id);
    }

    public function show($id)
    {
        $view = view('point-sales::app.sales.point.service.invoice.show');
        $view->invoice = Invoice::find($id);
        $view->list_invoice_archived = Invoice::joinFormulir()->archived($view->invoice->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_invoice_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-sales::app.sales.point.service.invoice.archived');
        $view->invoice_archived = Invoice::find($id);
        $view->invoice = Invoice::joinFormulir()->notArchived($view->invoice_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    //edit created sales invoice basic feature
    public function edit($id)
    {
        $view = view('point-sales::app.sales.point.service.invoice.edit');
        $view->invoice = Invoice::find($id);
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    // update store created sales invoice basic feature
    public function update(ServiceInvoiceRequest $request, $id)
    {
        DB::beginTransaction();

        $invoice = Invoice::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.sales.invoice', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = ServiceInvoiceHelper::create($request, $formulir);
        timeline_publish('update.invoice', 'update invoice service '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('sales/point/service/invoice/'.$invoice->id);
    }

    public function sendEmail(Request $request)
    {
        $id = app('request')->input('invoice_id');
        $invoice = Invoice::joinPerson()->where('point_sales_service_invoice.id', $id)->select('point_sales_service_invoice.*')->first();
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
            \Mail::send('point-sales::app.emails.sales.point.external.service-invoice', $data, function ($message) use ($invoice, $warehouse, $data, $name) {
                $message->to($invoice->person->email)->subject($name);
                $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.service-invoice-pdf', $data);
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

        $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.service-invoice-pdf', $data);
        return $pdf->download($invoice->formulir->form_number.'.pdf');
    }
}

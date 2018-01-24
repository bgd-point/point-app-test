<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

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
use Point\PointPurchasing\Helpers\ServiceInvoiceHelper;
use Point\PointPurchasing\Http\Requests\ServiceInvoiceRequest;
use Point\PointPurchasing\Models\Service\Invoice;
use Point\PointPurchasing\Models\Service\InvoiceItem;
use Point\PointPurchasing\Models\Service\InvoiceService;

class InvoiceController extends Controller
{
    use ValidationTrait;
    
    public function index()
    {
        $view = view('point-purchasing::app.purchasing.point.service.invoice.index');
        $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);

        $array_invoice_id = [];
        $view->array_invoice_id = $array_invoice_id;
        return $view;
    }

    public function ajaxDetailItem(Request $request, $id)
    {
        access_is_allowed('read.point.purchasing.service.invoice');
        $list_invoice_item = InvoiceItem::select('item.name as item_name',
            'point_purchasing_service_invoice_item.quantity',
            'point_purchasing_service_invoice_item.unit',
            'point_purchasing_service_invoice_item.price',
            'point_purchasing_service_invoice_id'
        )->joinItem()->joinInvoice()->where(
            'point_purchasing_service_invoice_item.point_purchasing_service_invoice_id', '=', $id
        );
        \Log::info($list_invoice_item);
        $list_invoice_service = InvoiceService::select('service.name as item_name',
            'point_purchasing_service_invoice_service.quantity as quantity ',
            'point_purchasing_service_invoice_service.service_notes as unit',
            'point_purchasing_service_invoice_service.price as price ',
            'point_purchasing_service_invoice_id'
        )->joinService()->joinInvoiceService()->where(
            'point_purchasing_service_invoice_service.point_purchasing_service_invoice_id', '=', $id
        );

        \Log::info($list_invoice_service);
        $results = $list_invoice_item->union($list_invoice_service)->get();
        return response()->json($results);
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.purchasing.service.invoice');

        $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-purchasing::app.purchasing.point.service.invoice.index-pdf', ['list_invoice' => $list_invoice]);
        return $pdf->stream();
    }

    public function create()
    {
        $view = view('point-purchasing::app.purchasing.point.service.invoice.create');
        $view->list_person = PersonHelper::getByType(['supplier']);
        $view->list_allocation = Allocation::active()->get();
        $person_type = PersonType::where('slug', 'supplier')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);
        return $view;
    }

    public function store(ServiceInvoiceRequest $request)
    {
        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.purchasing.service.invoice', date_format_db($request->input('form_date'), $request->input('time')), []);

        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-service-invoice');
        $invoice = ServiceInvoiceHelper::create($request, $formulir);
        timeline_publish('create.invoice', 'added new invoice service '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/service/invoice/'.$invoice->id);
    }

    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.service.invoice.show');
        $view->invoice = Invoice::find($id);
        $view->list_invoice_archived = Invoice::joinFormulir()->archived($view->invoice->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_invoice_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.service.invoice.archived');
        $view->invoice_archived = Invoice::find($id);
        $view->invoice = Invoice::joinFormulir()->notArchived($view->invoice_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    public function edit($id)
    {
        $view = view('point-purchasing::app.purchasing.point.service.invoice.edit');
        $view->invoice = Invoice::find($id);
        $view->person = Person::find($view->invoice->person_id);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::active()->get();
        return $view;
    }

    public function update(ServiceInvoiceRequest $request, $id)
    {
        DB::beginTransaction();

        $invoice = Invoice::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.service.invoice', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = ServiceInvoiceHelper::create($request, $formulir);
        timeline_publish('update.invoice', 'update invoice service '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/service/invoice/'.$invoice->id);
    }

    public function sendEmail(Request $request)
    {
        $id = app('request')->input('invoice_id');
        $invoice = Invoice::joinPerson()->where('point_purchasing_service_invoice.id', $id)->select('point_purchasing_service_invoice.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $invoice) {
            gritter_error('Failed, please select purchase invoice', 'false');
            return redirect()->back();
        }

        if (! $invoice->person->email) {
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
            \Mail::send('point-purchasing::emails.purchasing.point.external.service-invoice', $data, function ($message) use ($invoice, $warehouse, $data, $name) {
                $message->to($invoice->person->email)->subject($name);
                $pdf = \PDF::loadView('point-purchasing::emails.purchasing.point.external.service-invoice-pdf', $data);
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

        $pdf = \PDF::loadView('point-purchasing::emails.purchasing.point.external.service-invoice-pdf', $data);
        return $pdf->download($invoice->formulir->form_number.'.pdf');
    }
}

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
use Point\Framework\Models\EmailHistory;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Helpers\SalesQuotationHelper;
use Point\PointSales\Http\Requests\SalesRequest;
use Point\PointSales\Models\Sales\InvoiceItem;
use Point\PointSales\Models\Sales\SalesQuotation;

class SalesQuotationController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.quotation');

        $list_sales_quotation = SalesQuotation::joinFormulir()->joinPerson()->notArchived()->selectOriginal();

        $view = view('point-sales::app.sales.point.sales.sales-quotation.index');
        $view->list_sales_quotation = SalesQuotationHelper::searchList($list_sales_quotation, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_sales_quotation = $view->list_sales_quotation->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.quotation');
        $sales_quotation = SalesQuotation::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_sales_quotation = SalesQuotationHelper::searchList($sales_quotation, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.sales.sales-quotation.index-pdf', ['list_sales_quotation' => $list_sales_quotation]);
        
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.sales.quotation');

        $view = view('point-sales::app.sales.point.sales.sales-quotation.create');
        $view->list_employee = PersonHelper::getByType(['employee']);
        $view->list_customer = PersonHelper::getByType(['customer']);
        $view->list_allocation = Allocation::active()->get();
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
    public function store(SalesRequest $request)
    {
        FormulirHelper::isAllowedToCreate('create.point.sales.quotation', date_format_db($request->input('form_date')));

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-sales-quotation');
        $sales_quotation = SalesQuotationHelper::create($request, $formulir);
        timeline_publish('create.sales.quotation', 'added new sales quotation '  . $sales_quotation->formulir->form_number);

        DB::commit();

        gritter_success(trans('framework::framework/global.formulir.create.success'));
        return redirect('sales/point/indirect/sales-quotation/'.$sales_quotation->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.sales.quotation');

        $view = view('point-sales::app.sales.point.sales.sales-quotation.show');
        $sales_quotation = SalesQuotation::find($id);
        $view->sales_quotation = $sales_quotation;
        $view->list_sales_quotation_archived = SalesQuotation::joinFormulir()->archived($sales_quotation->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_sales_quotation_archived->count();
        $view->email_history = EmailHistory::where('formulir_id', $sales_quotation->formulir_id)->get();
        return $view;
    }

    /**
     * Display archived resource
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function archived($id)
    {
        access_is_allowed('read.point.sales.quotation');

        $view = view('point-sales::app.sales.point.sales.sales-quotation.archived');
        $view->sales_quotation_archived = SalesQuotation::find($id);
        $view->sales_quotation = SalesQuotation::joinFormulir()->notArchived($view->sales_quotation_archived->archived)->selectOriginal()->first();
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
        access_is_allowed('update.point.sales.quotation');

        $view = view('point-sales::app.sales.point.sales.sales-quotation.edit');
        $view->sales_quotation = SalesQuotation::find($id);
        $view->list_item = Item::active()->get();
        $view->list_employee= PersonHelper::getByType(['employee']);
        $view->list_customer = PersonHelper::getByType(['customer']);
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
    public function update(SalesRequest $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'person_id' => 'required',
            'approval_to' => 'required',
            'edit_notes' => 'required'
        ]);
        
        $sales_quotation = SalesQuotation::find($id);

        FormulirHelper::isAllowedToUpdate('update.point.sales.quotation', date_format_db($request['form_date']), $sales_quotation->formulir);

        DB::beginTransaction();

        $formulir_old = FormulirHelper::archive($request->input(), $sales_quotation->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $sales_quotation = SalesQuotationHelper::create($request, $formulir);
        timeline_publish('update.sales.quotation', 'update sales quotation ' . $sales_quotation->formulir->form_number);

        DB::commit();

        gritter_success(trans('framework::framework/global.formulir.update.success'));
        return redirect('sales/point/indirect/sales-quotation/' . $sales_quotation->id);
    }

    public function _getLastPrice()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $price = InvoiceItem::getLastPrice(\Input::get('item_id'));
        $response = array(
            'price' => number_format_quantity($price, 0),
            'status' => 'success'
        );

        return response()->json($response);
    }

    public function sendEmailQuotation(Request $request)
    {
        $id = app('request')->input('sales_quotation_id');
        $sales_quotation = SalesQuotation::joinPerson()->where('point_sales_quotation.id', $id)->select('point_sales_quotation.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $sales_quotation) {
            gritter_error('Failed, please select sales quotation', 'false');
            return redirect()->back();
        }

        if (! $sales_quotation->person->email) {
            gritter_error('Failed, please add email for customer', 'false');
            return redirect()->back();
        }

        $data = array(
            'sales_quotation' => $sales_quotation,
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'SALES QUOTATION ' . $sales_quotation->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $sales_quotation, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.external.sales-quotation', $data, function ($message) use ($sales_quotation, $warehouse, $data, $name) {
                $message->to($sales_quotation->person->email)->subject($name);
                $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.sales-quotation-pdf', $data)->setPaper('a4', 'landscape');
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email sales quotation', 'false');

        $email_history = new EmailHistory;
        $email_history->sender = auth()->id();
        $email_history->recipient = $sales_quotation->person_id;
        $email_history->recipient_email = $sales_quotation->person->email;
        $email_history->formulir_id = $sales_quotation->formulir_id;
        $email_history->sent_at = \Carbon\Carbon::now()->toDateTimeString();
        $email_history->save();
        
        return redirect()->back();
    }
}

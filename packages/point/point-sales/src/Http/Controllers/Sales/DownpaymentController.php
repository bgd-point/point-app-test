<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\FormulirLock;
use Point\PointFinance\Models\PaymentReference;
use Point\PointSales\Helpers\DownpaymentHelper;
use Point\PointSales\Models\Sales\Downpayment;
use Point\PointSales\Models\Sales\SalesOrder;

class DownpaymentController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.downpayment');

        $list_downpayment = Downpayment::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_downpayment = DownpaymentHelper::searchList($list_downpayment, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view = view('point-sales::app.sales.point.sales.downpayment.index');
        $view->list_downpayment = $list_downpayment->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.downpayment');
        $list_downpayment = Downpayment::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_downpayment = DownpaymentHelper::searchList($list_downpayment, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.sales.downpayment.index-pdf', ['list_downpayment' => $list_downpayment])->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function insert($id)
    {
        access_is_allowed('create.point.sales.downpayment');

        $view = view('point-sales::app.sales.point.sales.downpayment.create');

        if (!$id) {
            throw new PointException('DATA NOT FOUND');
        }
            
        $view->sales_order = "";

        $sales_order = SalesOrder::find($id);

        if ($sales_order) {
            $view->sales_order = $sales_order;
        }

        $view->list_person = PersonHelper::getByType(['customer']);
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function create()
    {
        access_is_allowed('create.point.sales.downpayment');

        $view = view('point-sales::app.sales.point.sales.downpayment.create');

        $view->sales_order = "";

        $view->list_person = PersonHelper::getByType(['customer']);
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
        $this->validate($request, [
            'form_date' => 'required',
            'person_id' => 'required',
            'amount' => 'required',
            'approval_to' => 'required',
        ]);

        if (number_format_db($request->input('amount')) < 1) {
            return redirect()->back()->withErrors('total amount must be greater than null')->withInput();
        }

        FormulirHelper::isAllowedToCreate('create.point.sales.downpayment', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-sales-downpayment');
        $downpayment = DownpaymentHelper::create($request, $formulir);

        timeline_publish('create.downpayment', 'create downpayment ' . $downpayment->formulir->form_number . ' success');

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('sales/point/indirect/downpayment/'.$downpayment->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.sales.downpayment');

        $view = view('point-sales::app.sales.point.sales.downpayment.show');
        $view->downpayment = Downpayment::find($id);
        $view->list_downpayment_archived = Downpayment::joinFormulir()->archived($view->downpayment->formulir->form_number)->get();
        $view->revision = $view->list_downpayment_archived->count();
        $view->list_referenced = FormulirLock::where('locked_id', '=', $view->downpayment->formulir_id)->where('locked', true)->get();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.sales.downpayment');

        $view = view('point-sales::app.sales.point.sales.downpayment.archived');
        $view->downpayment_archived = Downpayment::find($id);
        $view->downpayment = Downpayment::joinFormulir()->notArchived($view->downpayment_archived->formulir->archived)->selectOriginal()->first();
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
        access_is_allowed('update.point.sales.downpayment');

        $view = view('point-sales::app.sales.point.sales.downpayment.edit');

        $view->downpayment = Downpayment::find($id);
        $view->payment_reference = PaymentReference::where('payment_reference_id', '=', $view->downpayment->formulir_id)->first();

        $view->list_person = PersonHelper::getByType(['customer']);
        ;
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
            'person_id' => 'required',
            'approval_to' => 'required',
            'edit_notes' => 'required',
            'payment_type' => 'required',
        ]);

        if (number_format_db($request->input('amount')) < 1) {
            return redirect()->back()->withErrors('total amount must be greater than null')->withInput();
        }

        $downpayment = Downpayment::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.sales.downpayment', date_format_db($request->input('form_date')), $downpayment->formulir);

        DB::beginTransaction();

        PaymentReference::where('payment_reference_id', $downpayment->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $downpayment->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $downpayment = DownpaymentHelper::create($request, $formulir);
        timeline_publish('update.downpayment', 'update deposit ' . $downpayment->formulir->form_number . ' success');

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('sales/point/indirect/downpayment/'.$downpayment->id);
    }
}

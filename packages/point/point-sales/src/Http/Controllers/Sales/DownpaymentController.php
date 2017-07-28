<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffReceivableDetail;
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

    public function createFromCutoff()
    {
        access_is_allowed('create.point.sales.downpayment');
        $view = view('point-sales::app.sales.point.sales.downpayment.create-from-cutoff');
        $view->list_cutoff_account = CutOffAccount::joinFormulir()->notArchived()->approvalApproved()->close()->orderby('formulir.id', 'desc')->selectOriginal()->get();

        return $view;
    }

    public function _selectCutoff()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $cutoff_account = CutOffAccount::find(\Input::get('id'));
        $list_coa = [];
        foreach ($cutoff_account->cutOffAccountDetail as $cutoff_account_detail) {
            $position = JournalHelper::position($cutoff_account_detail->coa_id);
            if ($cutoff_account_detail->$position > 0 && $cutoff_account_detail->coa->has_subledger) {
                array_push($list_coa, ['text' => $cutoff_account_detail->coa->name . ' ( ' . number_format_quantity($cutoff_account_detail->$position) . ' )', 'value' => $cutoff_account_detail->coa_id]);
            }
        }

        $response = array(
            'lists' => $list_coa
        );
        return response()->json($response);
    }

    public function _selectAccount()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa_id = \Input::get('coa_id');
        $cut_off_account = CutOffAccount::find(\Input::get('cutoff_id'));

        $list_cutoff_account_detail = CutOffReceivableDetail::joinReceivable()
            ->joinFormulir()
            ->where('formulir.form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->where('formulir.form_status', 1)
            ->where('formulir.approval_status', 1)
            ->whereNotNull('formulir.form_number')
            ->where('point_accounting_cut_off_receivable_detail.subledger_type', '=', get_class(new Person()))
            ->select('point_accounting_cut_off_receivable_detail.*')
            ->get();
        $list_subledger = [];
        foreach ($list_cutoff_account_detail as $cutoff_account_detail) {
            array_push($list_subledger, ['text' => $cutoff_account_detail->person->codeName . ' ( ' . number_format_quantity($cutoff_account_detail->amount) . ' - '. $cutoff_account_detail->notes.' )', 'value' => $cutoff_account_detail->id]);
        }

        $response = array(
            'lists' => $list_subledger
        );
        return response()->json($response);
    }

    public function _selectCustomer()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $cutoff_account_detail = CutOffReceivableDetail::find(\Input::get('id'));
        $list_sales_order = SalesOrder::joinFormulir()->open()->notArchived()->approvalApproved()->where('is_cash', 1)->where('person_id', $cutoff_account_detail->subledger_id)->selectOriginal()->get();
        $list_order = [];
        foreach ($list_sales_order as $sales_order) {
            array_push($list_order, ['text' => $sales_order->formulir->form_number , 'value' => $sales_order->id]);
        }

        $response = array(
            'lists' => $list_order,
            'person_id' => $cutoff_account_detail->person->id,
            'cutoff_account_detail' => $cutoff_account_detail
        );

        return response()->json($response);
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
            'approval_to' => $request->input('close') ? '' : 'required',
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
            'approval_to' => $request->input('close') ? '' : 'required',
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

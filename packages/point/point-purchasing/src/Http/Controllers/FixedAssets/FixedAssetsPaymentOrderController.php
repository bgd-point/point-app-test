<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Faker\Provider\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Refer;
use Point\Framework\Models\SettingJournal;
use Point\PointFinance\Models\PaymentReference;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsPaymentOrderHelper;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsDownpayment;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPaymentOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsRetur;

class FixedAssetsPaymentOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.index');
        $list_payment_order = FixedAssetsPaymentOrder::joinFormulir()->notArchived()->selectOriginal()->orderByStandard();
        $list_payment_order = FixedAssetsPaymentOrderHelper::searchList($list_payment_order, \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_payment_order = $list_payment_order->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.create-step-1');
        $view->list_invoice = FixedAssetsInvoice::joinFormulir()
            ->availableToPaymentOrder()
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep2($supplier_id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.create-step-2');
        $view->supplier = Person::find($supplier_id);
        $view->list_invoice = FixedAssetsInvoice::availableToCreatePaymentOrder($supplier_id)->get();
        $view->list_downpayment = FixedAssetsDownpayment::availableToCreatePaymentOrder($supplier_id)->get();
        $view->list_retur = FixedAssetsRetur::availableToCreatePaymentOrder($supplier_id)->get();
        $view->list_item = Item::all();
        $view->list_coa = Coa::all();
        $view->list_allocation = Allocation::all();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function createStep3(Request $request)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
        ]);

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.create-step-3');
        $view->list_invoice = FixedAssetsInvoice::whereIn('formulir_id', \Input::get('invoice_id'))->get();
        $view->invoice_rid = \Input::get('invoice_rid');
        $view->invoice_id = \Input::get('invoice_id');
        $view->amount_invoice = number_format_db(\Input::get('amount_invoice'));
        $view->available_invoice = number_format_db(\Input::get('available_invoice'));
        $view->original_amount_invoice = number_format_db(\Input::get('original_amount_invoice'));

        $view->list_retur = FixedAssetsRetur::whereIn('formulir_id', \Input::get('retur_id'))->get();
        $view->retur_rid = \Input::get('retur_rid');
        $view->retur_id = \Input::get('retur_id');
        $view->amount_retur = number_format_db(\Input::get('amount_retur'));
        $view->available_retur = number_format_db(\Input::get('available_retur'));
        $view->original_amount_retur = number_format_db(\Input::get('original_amount_retur'));

        $view->list_downpayment = FixedAssetsDownpayment::whereIn('formulir_id', \Input::get('downpayment_id'))->get();
        $view->downpayment_rid = \Input::get('downpayment_rid');
        $view->downpayment_id = \Input::get('downpayment_id');
        $view->amount_downpayment = number_format_db(\Input::get('amount_downpayment'));
        $view->available_downpayment = number_format_db(\Input::get('available_downpayment'));
        $view->original_amount_downpayment = number_format_db(\Input::get('original_amount_downpayment'));

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->supplier = Person::find(\Input::get('supplier_id'));
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->other_notes = \Input::get('other_notes');
        $view->payment_type = \Input::get('payment_type');
        $view->allocation_id = \Input::get('allocation_id');
        $view->total = number_format_db(\Input::get('total'));

        $view->notes = \Input::get('notes');
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
        DB::beginTransaction();

        $references_id = [];
        $references_type = [];
        $references = [];
        $references_amount = [];
        $references_amount_original = [];
        $references_notes = [];
        $references_account = [];
        $formulir_id = [];
        $invoice_id = $request->input('invoice_id');
        for ($i=0;$i < count($invoice_id);$i++) {
            $reference_type = get_class(new FixedAssetsInvoice);
            array_push($formulir_id, FixedAssetsInvoice::find($invoice_id[$i])->formulir_id);
            array_push($references_id, $invoice_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point purchasing fixed assets')->where('name', 'account payable')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }
        $retur_id = $request->input('retur_id');
        for ($i=0;$i < count($retur_id);$i++) {
            $reference_type = get_class(new FixedAssetsRetur);
            array_push($formulir_id, FixedAssetsRetur::find($retur_id[$i])->formulir_id);
            array_push($references_id, $retur_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_amount, $request->input('retur_amount')[$i]);
            array_push($references_amount_original, $request->input('retur_amount_original')[$i]);
            array_push($references_notes, $request->input('retur_notes')[$i]);
            array_push($references, $reference_type::find($retur_id[$i]));
        }
        $downpayment_id = $request->input('downpayment_id');
        for ($i=0;$i < count($downpayment_id);$i++) {
            $reference_type = get_class(new FixedAssetsDownpayment);
            array_push($formulir_id, FixedAssetsDownpayment::find($downpayment_id[$i])->formulir_id);
            array_push($references_id, $downpayment_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point purchasing fixed assets')->where('name', 'purchase downpayment')->first()->coa_id);
            array_push($references_amount, $request->input('downpayment_amount')[$i]);
            array_push($references_amount_original, $request->input('downpayment_amount_original')[$i]);
            array_push($references_notes, $request->input('downpayment_notes')[$i]);
            array_push($references, $reference_type::find($downpayment_id[$i]));
        }

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));
        access_is_allowed('create.point.purchasing.payment.order.fixed.assets', date_format_db($request->input('form_date'),
            $request->input('time')), $formulir_id);

        $formulir = FormulirHelper::create($request, 'point-purchasing-payment-order-fixed-assets');
        $payment_order = FixedAssetsPaymentOrderHelper::create($request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes);
        timeline_publish('create.point.purchasing.payment.order.fixed.assets', 'added new payment order fixed assets '  . $payment_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/fixed-assets/payment-order/'.$payment_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.show');
        $view->payment_order = FixedAssetsPaymentOrder::find($id);
        $view->list_payment_order_archived = FixedAssetsPaymentOrder::joinFormulir()->archived($view->payment_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_payment_order_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.archived');
        $view->payment_order_archived = FixedAssetsPaymentOrder::find($id);
        $view->payment_order = FixedAssetsPaymentOrder::joinFormulir()->notArchived($view->payment_order_archived->formulir->archived)->selectOriginal()->first();
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
        $payment_order = FixedAssetsPaymentOrder::find($id);
        $invoice_edit = ReferHelper::getRefersId(get_class(new FixedAssetsInvoice), get_class($payment_order), $payment_order->id);
        $downpayment_edit = ReferHelper::getRefersId(get_class(new FixedAssetsDownpayment), get_class($payment_order), $payment_order->id);
        $retur_edit = ReferHelper::getRefersId(get_class(new FixedAssetsRetur), get_class($payment_order), $payment_order->id);
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.edit');
        $view->payment_order = $payment_order;
        $view->supplier = $payment_order->supplier;
        $view->list_invoice = FixedAssetsInvoice::joinFormulir()
            ->joinSupplier()
            ->notArchived()
            ->availableToEditPaymentOrder($payment_order->supplier_id, $invoice_edit)
            ->selectOriginal()
            ->get();
        $view->list_downpayment = FixedAssetsDownpayment::joinFormulir()
            ->joinSupplier()
            ->notArchived()
            ->availableToEditPaymentOrder($payment_order->supplier_id, $downpayment_edit)
            ->selectOriginal()
            ->get();
        $view->list_retur = FixedAssetsRetur::joinFormulir()
            ->joinSupplier()
            ->notArchived()
            ->availableToEditPaymentOrder($payment_order->supplier_id, $retur_edit)
            ->selectOriginal()
            ->get();
        $view->payment_reference = PaymentReference::where('payment_reference_id', '=', $payment_order->formulir_id)->first();
        $view->list_item = Item::all();
        $view->list_coa = Coa::all();
        $view->list_allocation = Allocation::all();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function editReview(Request $request, $id)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
        ]);

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.payment-order.edit-review');
        $view->payment_order = FixedAssetsPaymentOrder::find($id);
        $view->list_invoice = FixedAssetsInvoice::whereIn('formulir_id', \Input::get('invoice_id'))->get();
        $view->invoice_rid = \Input::get('invoice_rid');
        $view->invoice_id = \Input::get('invoice_id');
        $view->amount_invoice = number_format_db(\Input::get('amount_invoice'));
        $view->available_invoice = number_format_db(\Input::get('available_invoice'));
        $view->original_amount_invoice = number_format_db(\Input::get('original_amount_invoice'));
        $view->invoice_amount_edit = number_format_db(\Input::get('invoice_amount_edit'));

        $view->list_retur = FixedAssetsRetur::whereIn('formulir_id', \Input::get('retur_id'))->get();
        $view->retur_rid = \Input::get('retur_rid');
        $view->retur_id = \Input::get('retur_id');
        $view->amount_retur = number_format_db(\Input::get('amount_retur'));
        $view->available_retur = number_format_db(\Input::get('available_retur'));
        $view->original_amount_retur = number_format_db(\Input::get('original_amount_retur'));
        $view->retur_amount_edit = number_format_db(\Input::get('retur_amount_edit'));

        $view->list_downpayment = FixedAssetsDownpayment::whereIn('formulir_id', \Input::get('downpayment_id'))->get();
        $view->downpayment_rid = \Input::get('downpayment_rid');
        $view->downpayment_id = \Input::get('downpayment_id');
        $view->amount_downpayment = number_format_db(\Input::get('amount_downpayment'));
        $view->available_downpayment = number_format_db(\Input::get('available_downpayment'));
        $view->original_amount_downpayment = number_format_db(\Input::get('original_amount_downpayment'));
        $view->downpayment_amount_edit = number_format_db(\Input::get('downpayment_amount_edit'));

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->supplier = Person::find(\Input::get('supplier_id'));
        $view->payment_type = \Input::get('payment_type');
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->other_notes = \Input::get('other_notes');
        $view->allocation_id = \Input::get('allocation_id');
        $view->total = number_format_db(\Input::get('total'));

        $view->notes = \Input::get('notes');
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
        DB::beginTransaction();

        $references_id = [];
        $references_type = [];
        $references = [];
        $references_amount = [];
        $references_amount_original = [];
        $references_amount_edit = [];
        $references_notes = [];
        $references_account = [];
        $formulir_id = [];
        $invoice_id = $request->input('invoice_id');
        for ($i=0;$i < count($invoice_id);$i++) {
            $reference_type = get_class(new FixedAssetsInvoice);
            array_push($formulir_id, FixedAssetsInvoice::find($invoice_id[$i])->formulir_id);
            array_push($references_id, $invoice_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point purchasing fixed assets')->where('name', 'account payable')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('invoice_amount_edit')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }
        $retur_id = $request->input('retur_id');
        for ($i=0;$i < count($retur_id);$i++) {
            $reference_type = get_class(new FixedAssetsRetur);
            array_push($formulir_id, FixedAssetsRetur::find($retur_id[$i])->formulir_id);
            array_push($references_id, $retur_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_amount, $request->input('retur_amount')[$i]);
            array_push($references_amount_original, $request->input('retur_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('retur_amount_edit')[$i]);
            array_push($references_notes, $request->input('retur_notes')[$i]);
            array_push($references, $reference_type::find($retur_id[$i]));
        }
        $downpayment_id = $request->input('downpayment_id');
        for ($i=0;$i < count($downpayment_id);$i++) {
            $reference_type = get_class(new FixedAssetsDownpayment);
            array_push($formulir_id, FixedAssetsDownpayment::find($downpayment_id[$i])->formulir_id);
            array_push($references_id, $downpayment_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point purchasing fixed assets')->where('name', 'purchase downpayment')->first()->coa_id);
            array_push($references_amount, $request->input('downpayment_amount')[$i]);
            array_push($references_amount_original, $request->input('downpayment_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('downpayment_amount_edit')[$i]);
            array_push($references_notes, $request->input('downpayment_notes')[$i]);
            array_push($references, $reference_type::find($downpayment_id[$i]));
        }

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));

        $payment_order = FixedAssetsPaymentOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.payment.order.fixed.assets', date_format_db($request->input('form_date'), $request->input('time')), $payment_order->formulir);

        PaymentReference::where('payment_reference_id', $payment_order->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $payment_order->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $payment_order = FixedAssetsPaymentOrderHelper::create($request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes, $references_amount_edit);
        timeline_publish('update.payment.order.fixed.assets', 'added new payment order fixed assets '  . $payment_order->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/fixed-assets/payment-order/'.$payment_order->id);
    }
}

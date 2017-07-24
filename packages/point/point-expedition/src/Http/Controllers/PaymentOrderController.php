<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\AccountPayableAndReceivableHelper;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\Framework\Models\SettingJournal;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffPayableDetail;
use Point\PointExpedition\Helpers\PaymentOrderHelper;
use Point\PointExpedition\Models\Downpayment;
use Point\PointExpedition\Models\Invoice;
use Point\PointExpedition\Models\PaymentOrder;
use Point\PointFinance\Models\PaymentReference;

class PaymentOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('point-expedition::app.expedition.point.payment-order.index');
        $list_payment_order = PaymentOrder::joinFormulir()->joinExpedition()->notArchived()->selectOriginal();
        $list_payment_order = PaymentOrderHelper::searchList($list_payment_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
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
        $view = view('point-expedition::app.expedition.point.payment-order.create-step-1');
        $view->list_invoice = Invoice::availableToPaymentOrder()->paginate(100);

        $view->list_cut_off_payable = CutOffPayableDetail::joinPayable()
            ->joinFormulir()
            ->where('formulir.form_status', 1)
            ->where('formulir.approval_status', 1)
            ->whereNotNull('formulir.form_number')
            ->where('point_accounting_cut_off_payable_detail.subledger_type', '=', get_class(new Person()))
            ->select('point_accounting_cut_off_payable_detail.*')
            ->groupBy('point_accounting_cut_off_payable_detail.subledger_id')
            ->get();

        return $view;
    }

    public function createStep2($expedition_id)
    {
        $view = view('point-expedition::app.expedition.point.payment-order.create-step-2');
        $view->expedition = Person::find($expedition_id);
        $view->list_invoice = Invoice::availableToCreatePaymentOrder($expedition_id)->get();
        $view->list_downpayment = Downpayment::availableToCreatePaymentOrder($expedition_id)->get();
        $view->list_item = Item::active()->get();
        $view->list_coa = Coa::getNonSubledger();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_cut_off_payable = CutOffPayableDetail::joinPayable()
            ->joinFormulir()
            ->where('formulir.form_status', 1)
            ->where('formulir.approval_status', 1)
            ->whereNotNull('formulir.form_number')
            ->where('point_accounting_cut_off_payable_detail.subledger_type', '=', get_class(new Person()))
            ->where('point_accounting_cut_off_payable_detail.subledger_id', '=', $expedition_id)
            ->select('point_accounting_cut_off_payable_detail.*')
            ->get();

        return $view;
    }

    public function createStep3(Request $request)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
            'payment_type' => 'required'
        ]);

        if ($request->input('total_payment') == "") {
            gritter_error('coloum total payment can not empty');
            return redirect('expedition/point/payment-order/create-step-2/' . \Input::get('expedition_id'));
        }

        $view = view('point-expedition::app.expedition.point.payment-order.create-step-3');
        $view->list_invoice = Invoice::whereIn('formulir_id', \Input::get('invoice_id'))->get();
        $view->invoice_rid = \Input::get('invoice_rid');
        $view->invoice_id = \Input::get('invoice_id');
        $view->invoice_reference_id = \Input::get('invoice_reference_id');
        $view->invoice_reference_type = \Input::get('invoice_reference_type');
        $view->amount_invoice = number_format_db(\Input::get('amount_invoice'));
        $view->available_invoice = number_format_db(\Input::get('available_invoice'));
        $view->original_amount_invoice = number_format_db(\Input::get('original_amount_invoice'));

        $view->list_downpayment = Downpayment::whereIn('formulir_id', \Input::get('downpayment_id'))->get();
        $view->downpayment_rid = \Input::get('downpayment_rid');
        $view->downpayment_id = \Input::get('downpayment_id');
        $view->downpayment_reference_id = \Input::get('downpayment_reference_id');
        $view->downpayment_reference_type = \Input::get('downpayment_reference_type');
        $view->amount_downpayment = number_format_db(\Input::get('amount_downpayment'));
        $view->available_downpayment = number_format_db(\Input::get('available_downpayment'));
        $view->original_amount_downpayment = number_format_db(\Input::get('original_amount_downpayment'));

        $view->list_cut_off_payable = CutOffPayableDetail::whereIn('id', \Input::get('cut_off_id'))->get();
        $view->cutoff_rid = \Input::get('cut_off_rid');
        $view->cutoff_id = \Input::get('cut_off_id');
        $view->cutoff_reference_id = \Input::get('cutoff_reference_id');
        $view->cutoff_reference_type = \Input::get('cutoff_reference_type');
        $view->amount_cutoff = number_format_db(\Input::get('amount_cutoff'));
        $view->available_cutoff = number_format_db(\Input::get('available_cutoff'));
        $view->original_amount_cutoff = number_format_db(\Input::get('original_amount_cutoff'));

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->expedition = Person::find(\Input::get('expedition_id'));
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->coa_notes = \Input::get('coa_notes');
        $view->allocation_id = \Input::get('allocation_id');
        $view->total = number_format_db(\Input::get('total'));

        $view->notes = \Input::get('notes');
        $view->payment_type = \Input::get('payment_type');

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
        DB::beginTransaction();

        $references_id = [];
        $references_type = [];
        $references = [];
        $references_account = [];
        $references_amount = [];
        $references_amount_original = [];
        $references_notes = [];
        $references_detail_id = [];
        $references_detail_type = [];
        $formulir_id = [];
        $invoice_id = $request->input('invoice_id');
        for ($i = 0; $i < count($invoice_id); $i++) {
            $reference_type = get_class(new Invoice);
            array_push($formulir_id, Invoice::find($invoice_id[$i])->formulir_id);
            array_push($references_id, $invoice_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_detail_id, $request->input('invoice_reference_id')[$i]);
            array_push($references_detail_type, $request->input('invoice_reference_type')[$i]);
            array_push($references_account, SettingJournal::where('group', 'point expedition')->where('name', 'Account Payable - Expedition')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }

        $downpayment_id = $request->input('downpayment_id');
        for ($i = 0; $i < count($downpayment_id); $i++) {
            $reference_type = get_class(new Downpayment);
            array_push($formulir_id, Downpayment::find($downpayment_id[$i])->formulir_id);
            array_push($references_id, $downpayment_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_detail_id, $request->input('downpayment_reference_id')[$i]);
            array_push($references_detail_type, $request->input('downpayment_reference_type')[$i]);
            array_push($references_account, SettingJournal::where('group', 'point expedition')->where('name', 'Expedition Downpayment')->first()->coa_id);
            array_push($references_amount, $request->input('downpayment_amount')[$i]);
            array_push($references_amount_original, $request->input('downpayment_amount_original')[$i]);
            array_push($references_notes, $request->input('downpayment_notes')[$i]);
            array_push($references, $reference_type::find($downpayment_id[$i]));
        }

        $cutoff_id = $request->input('cutoff_id');
        for ($i=0;$i < count($cutoff_id);$i++) {
            $reference_type = get_class(new CutOffPayableDetail);
            array_push($formulir_id, CutOffPayableDetail::find($cutoff_id[$i])->cutoffPayable->formulir_id);
            array_push($references_id, $cutoff_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_detail_id, $request->input('cutoff_reference_id')[$i]);
            array_push($references_detail_type, $request->input('cutoff_reference_type')[$i]);
            array_push($references_account, CutOffPayableDetail::find($cutoff_id[$i])->coa_id);
            array_push($references_amount, $request->input('cutoff_amount')[$i]);
            array_push($references_amount_original, $request->input('cutoff_amount_original')[$i]);
            array_push($references_notes, $request->input('cutoff_notes')[$i]);
            array_push($references, $reference_type::find($cutoff_id[$i]));
        }

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));

        access_is_allowed('create.point.expedition.payment.order',
            date_format_db($request->input('form_date'), $request->input('time')), $formulir_id);

        $formulir = FormulirHelper::create($request->input(), 'point-expedition-payment-order');
        $payment_order = PaymentOrderHelper::create($request, $formulir, $references, $references_type, $references_id,
            $references_account, $references_amount, $references_amount_original, $references_notes, $references_detail_id, $references_detail_type);
        timeline_publish('create.payment.order', 'added new payment order ' . $payment_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success');
        return redirect('expedition/point/payment-order/' . $payment_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-expedition::app.expedition.point.payment-order.show');
        $view->payment_order = PaymentOrder::find($id);
        $view->list_payment_order_archived = PaymentOrder::joinFormulir()->archived($view->payment_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_payment_order_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-expedition::app.expedition.point.payment-order.archived');
        $view->payment_order_archived = PaymentOrder::find($id);
        $view->payment_order = PaymentOrder::joinFormulir()->notArchived($view->payment_order_archived->formulir->archived)->selectOriginal()->first();
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
        $payment_order = PaymentOrder::find($id);
        $invoice_edit = ReferHelper::getRefersId(get_class(new Invoice), get_class($payment_order), $payment_order->id);
        $downpayment_edit = ReferHelper::getRefersId(get_class(new Downpayment), get_class($payment_order), $payment_order->id);
        $cutoff_edit = ReferHelper::getRefersId(get_class(new CutOffPayableDetail), get_class($payment_order), $payment_order->id);
        $view = view('point-expedition::app.expedition.point.payment-order.edit');
        $view->payment_order = $payment_order;
        $view->expedition = $payment_order->expedition;
        $view->list_invoice = Invoice::availableToEditPaymentOrder($payment_order->expedition_id, $invoice_edit)->get();
        $view->list_downpayment = Downpayment::availableToEditPaymentOrder($payment_order->expedition_id, $downpayment_edit)->get();
        $view->list_coa = Coa::getNonSubledger();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_cut_off_payable = CutOffPayableDetail::joinPayable()
            ->joinFormulir()
            ->where('formulir.form_status', 1)
            ->where('formulir.approval_status', 1)
            ->whereNotNull('formulir.form_number')
            ->where('point_accounting_cut_off_payable_detail.subledger_type', '=', get_class(new Person()))
            ->select('point_accounting_cut_off_payable_detail.*')
            ->where('point_accounting_cut_off_payable_detail.subledger_id', '=', $payment_order->expedition_id)
            ->orWhereIn('point_accounting_cut_off_payable_detail.id', $cutoff_edit)
            ->get();
        return $view;
    }

    public function editReview(Request $request, $id)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
            'edit_notes' => 'required',
        ]);

        $view = view('point-expedition::app.expedition.point.payment-order.edit-review');
        $view->reason_edit = \Input::get('edit_notes');
        $view->payment_order = PaymentOrder::find($id);
        $view->list_invoice = Invoice::whereIn('formulir_id', \Input::get('invoice_id'))->get();
        $view->invoice_rid = \Input::get('invoice_rid');
        $view->invoice_id = \Input::get('invoice_id');
        $view->amount_invoice = number_format_db(\Input::get('amount_invoice'));
        $view->available_invoice = number_format_db(\Input::get('available_invoice'));
        $view->original_amount_invoice = number_format_db(\Input::get('original_amount_invoice'));
        $view->invoice_amount_edit = number_format_db(\Input::get('invoice_amount_edit'));

        $view->list_downpayment = Downpayment::whereIn('formulir_id', \Input::get('downpayment_id'))->get();
        $view->downpayment_rid = \Input::get('downpayment_rid');
        $view->downpayment_id = \Input::get('downpayment_id');
        $view->amount_downpayment = number_format_db(\Input::get('amount_downpayment'));
        $view->available_downpayment = number_format_db(\Input::get('available_downpayment'));
        $view->original_amount_downpayment = number_format_db(\Input::get('original_amount_downpayment'));
        $view->downpayment_amount_edit = number_format_db(\Input::get('downpayment_amount_edit'));

        $view->list_cut_off_payable = CutOffPayableDetail::whereIn('id', \Input::get('cut_off_id'))->get();
        $view->cutoff_rid = \Input::get('cut_off_rid');
        $view->cutoff_id = \Input::get('cut_off_id');
        $view->amount_cutoff = number_format_db(\Input::get('amount_cutoff'));
        $view->available_cutoff = number_format_db(\Input::get('available_cutoff'));
        $view->original_amount_cutoff = number_format_db(\Input::get('original_amount_cutoff'));
        $view->cutoff_amount_edit = number_format_db(\Input::get('cutoff_amount_edit'));

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->expedition = Person::find(\Input::get('expedition_id'));
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->coa_notes = \Input::get('coa_notes');
        $view->allocation_id = \Input::get('allocation_id');
        $view->total = number_format_db(\Input::get('total'));

        $view->notes = \Input::get('notes');
        $view->payment_type = \Input::get('payment_type');
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
        DB::beginTransaction();

        $references_id = [];
        $references_type = [];
        $references = [];
        $references_account = [];
        $references_amount = [];
        $references_amount_original = [];
        $references_amount_edit = [];
        $references_notes = [];
        $formulir_id = [];
        $invoice_id = $request->input('invoice_id');
        for ($i = 0; $i < count($invoice_id); $i++) {
            $reference_type = get_class(new Invoice);
            array_push($formulir_id, Invoice::find($invoice_id[$i])->formulir_id);
            array_push($references_id, $invoice_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point expedition')->where('name', 'Account Payable - Expedition')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('invoice_amount_edit')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }

        $downpayment_id = $request->input('downpayment_id');
        for ($i = 0; $i < count($downpayment_id); $i++) {
            $reference_type = get_class(new Downpayment);
            array_push($formulir_id, Downpayment::find($downpayment_id[$i])->formulir_id);
            array_push($references_id, $downpayment_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point expedition')->where('name', 'Expedition Downpayment')->first()->coa_id);
            array_push($references_amount, $request->input('downpayment_amount')[$i]);
            array_push($references_amount_original, $request->input('downpayment_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('downpayment_amount_edit')[$i]);
            array_push($references_notes, $request->input('downpayment_notes')[$i]);
            array_push($references, $reference_type::find($downpayment_id[$i]));
        }

        $cutoff_id = $request->input('cutoff_id');
        for ($i=0;$i < count($cutoff_id);$i++) {
            $reference_type = get_class(new CutOffPayableDetail);
            array_push($formulir_id, CutOffPayableDetail::find($cutoff_id[$i])->cutoffPayable->formulir_id);
            array_push($references_id, $cutoff_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, CutOffPayableDetail::find($cutoff_id[$i])->coa_id);
            array_push($references_amount, $request->input('cutoff_amount')[$i]);
            array_push($references_amount_original, $request->input('cutoff_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('cutoff_amount_edit')[$i]);
            array_push($references_notes, $request->input('cutoff_notes')[$i]);
            array_push($references, $reference_type::find($cutoff_id[$i]));
        }
        
        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));

        $payment_order = PaymentOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.expedition.payment.order', date_format_db($request->input('form_date'), $request->input('time')), $payment_order->formulir);
        PaymentReference::where('payment_reference_id', $payment_order->formulir_id)->delete();

        $formulir_old = self::archive($request->input(), $payment_order->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $payment_order = PaymentOrderHelper::create(
            $request,
            $formulir,
            $references,
            $references_type,
            $references_id,
            $references_account,
            $references_amount,
            $references_amount_original,
            $references_notes,
            $references_amount_edit
        );
        timeline_publish('update.payment.order', 'added new payment order ' . $payment_order->formulir->form_number);

        DB::commit();

        gritter_success('update form success');
        return redirect('expedition/point/payment-order/' . $payment_order->id);
    }

    public static function archive($request, $formulir_id)
    {
        $formulir_old = Formulir::find($formulir_id);
        $formulir_old->archived = $formulir_old->form_number;
        $formulir_old->form_number = null;
        $formulir_old->edit_notes = array_key_exists('edit_notes', $request) ? $request['edit_notes'] : '';
        $formulir_old->updated_by = $request['user']->id;
        if (!$formulir_old->save()) {
            gritter_error('create has been failed', false);
        }

        self::unlock($formulir_id);
        ReferHelper::cancel($formulir_old->formulirable_type, $formulir_old->formulirable_id);
        InventoryHelper::remove($formulir_id);
        JournalHelper::remove($formulir_id);
        AccountPayableAndReceivableHelper::remove($formulir_id);
        AllocationHelper::remove($formulir_id);
        FormulirHelper::cancelPaymentReference($formulir_id);

        return $formulir_old;
    }

    public static function unlock($locking_id)
    {
        $list_formulir_lock = FormulirLock::where('locking_id', '=', $locking_id)->get();
        foreach ($list_formulir_lock as $formulir_lock) {
            $locked_form = Formulir::find($formulir_lock->locked_id);
            if ($locked_form->formulirable_type != get_class(new Downpayment)
                && $locked_form->formulirable_type != get_class(new CutOffPayable)) {
                $locked_form->form_status = 0;
                $locked_form->save();    
            }
            
            $formulir_lock->locked = false;
            $formulir_lock->save();
        }

        return true;
    }

    public function sendEmailPayment(Request $request)
    {
        $id = app('request')->input('payment_order_id');
        $payment_order = PaymentOrder::joinExpedition()->where('point_expedition_payment_order.id', $id)->select('point_expedition_payment_order.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $payment_order) {
            gritter_error('Failed, please select expedition payment order', 'false');
            return redirect()->back();
        }

        if (! $payment_order->expedition->email) {
            gritter_error('Failed, please add email for expedition', 'false');
            return redirect()->back();
        }

        $data = array(
            'payment_order' => $payment_order, 
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'EXPEDITION PAYMENT ORDER '. $payment_order->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $payment_order, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-expedition::emails.expedition.point.external.payment-order', $data, function ($message) use ($payment_order, $warehouse, $data, $name) {
                $message->to($payment_order->expedition->email)->subject($name);
                $pdf = \PDF::loadView('point-expedition::emails.expedition.point.external.payment-order-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email payment order', 'false');
        return redirect()->back();
    }
}

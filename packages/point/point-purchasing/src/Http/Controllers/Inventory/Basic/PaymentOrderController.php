<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory\Basic;

use App\Http\Controllers\Controller;
use Faker\Provider\Payment;
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
use Point\Framework\Models\Refer;
use Point\Framework\Models\SettingJournal;
use Point\PointAccounting\Models\CutOffPayableDetail;
use Point\PointFinance\Models\PaymentReference;
use Point\PointPurchasing\Helpers\BasicPaymentOrderHelper;
use Point\PointPurchasing\Models\Inventory\Downpayment;
use Point\PointPurchasing\Models\Inventory\Basic\Invoice;
use Point\PointPurchasing\Models\Inventory\Basic\PaymentOrder;
use Point\PointPurchasing\Models\Inventory\Basic\PaymentOrderDetail;
use Point\PointPurchasing\Models\Inventory\Basic\PaymentOrderLain;
use Point\PointPurchasing\Models\Inventory\Retur;

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
        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.index');
        $list_payment_order = PaymentOrder::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();
        $list_payment_order = BasicPaymentOrderHelper::searchList($list_payment_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
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
        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.create-step-1');
        $view->list_invoice = Invoice::joinFormulir()
            ->availableToPaymentOrder()
            ->selectOriginal()
            ->paginate(100);

        return $view;
    }

    public function createStep2($supplier_id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.create-step-2');
        $view->supplier = Person::find($supplier_id);
        $view->list_invoice = Invoice::availableToCreatePaymentOrder($supplier_id)->get();
        $view->list_coa = Coa::getNonSubledger();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        
        return $view;
    }

    public function createStep3(Request $request)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
        ]);

        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.create-step-3');
        $view->list_invoice = Invoice::whereIn('formulir_id', \Input::get('invoice_id'))->get();
        $view->invoice_rid = \Input::get('invoice_rid');
        $view->invoice_id = \Input::get('invoice_id');
        $view->amount_invoice = number_format_db(\Input::get('amount_invoice'));
        $view->available_invoice = number_format_db(\Input::get('available_invoice'));
        $view->original_amount_invoice = number_format_db(\Input::get('original_amount_invoice'));

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->supplier = Person::find(\Input::get('supplier_id'));
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->coa_notes = \Input::get('coa_notes');
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
            $reference_type = get_class(new Invoice);
            array_push($formulir_id, Invoice::find($invoice_id[$i])->formulir_id);
            array_push($references_id, $invoice_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point purchasing')->where('name', 'account payable')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));
        access_is_allowed('create.point.purchasing.basic.payment.order', date_format_db($request->input('form_date'), $request->input('time')), $formulir_id);
        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-payment-order');
        $payment_order = BasicPaymentOrderHelper::create($request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes);
        timeline_publish('create.payment.order', 'added new payment order '  . $payment_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/payment-order/basic/'.$payment_order->id.'/show');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.show');
        $view->payment_order = PaymentOrder::find($id);
        $view->list_payment_order_archived = PaymentOrder::joinFormulir()->archived($view->payment_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_payment_order_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.archived');
        $view->payment_order_archived = PaymentOrder::find($id);
        $view->payment_order = PaymentOrder::joinFormulir()->notArchived($view->payment_order_archived->formulir->archived)->selectOriginal()->first();
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
        $payment_order = PaymentOrder::find($id);
        $invoice_edit = ReferHelper::getRefersId(get_class(new Invoice), get_class($payment_order), $payment_order->id);
        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.edit');
        $view->payment_order = $payment_order;
        $view->supplier = $payment_order->supplier;
        $view->list_invoice = Invoice::joinFormulir()
            ->joinSupplier()
            ->notArchived()
            ->availableToEditPaymentOrder($payment_order->supplier_id, $invoice_edit)
            ->selectOriginal()
            ->get();
        
        $view->list_coa = Coa::getNonSubledger();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function editReview(Request $request, $id)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
        ]);

        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.basic.edit-review');
        $view->payment_order = PaymentOrder::find($id);
        $view->list_invoice = Invoice::whereIn('formulir_id', \Input::get('invoice_id'))->get();
        $view->invoice_rid = \Input::get('invoice_rid');
        $view->invoice_id = \Input::get('invoice_id');
        $view->amount_invoice = number_format_db(\Input::get('amount_invoice'));
        $view->available_invoice = number_format_db(\Input::get('available_invoice'));
        $view->original_amount_invoice = number_format_db(\Input::get('original_amount_invoice'));
        $view->invoice_amount_edit = number_format_db(\Input::get('invoice_amount_edit'));

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->supplier = Person::find(\Input::get('supplier_id'));
        $view->payment_type = \Input::get('payment_type');
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->coa_notes = \Input::get('coa_notes');
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
            $reference_type = get_class(new Invoice);
            array_push($formulir_id, Invoice::find($invoice_id[$i])->formulir_id);
            array_push($references_id, $invoice_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point purchasing')->where('name', 'account payable')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('invoice_amount_edit')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }
        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));

        $payment_order = PaymentOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.basic.payment.order', date_format_db($request->input('form_date'), $request->input('time')), $payment_order->formulir);
        PaymentReference::where('payment_reference_id', $payment_order->formulir_id)->delete();

        $formulir_old = FormulirHelper::archive($request->input(), $payment_order->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $payment_order = BasicPaymentOrderHelper::create($request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes, $references_amount_edit);
        timeline_publish('update.payment.order', 'added new payment order '  . $payment_order->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/payment-order/basic/'.$payment_order->id.'/show');
    }

    public function sendEmailPayment(Request $request)
    {
        $id = app('request')->input('payment_order_id');
        $payment_order = PaymentOrder::joinSupplier()->where('point_purchasing_basic_payment_order.id', $id)->select('point_purchasing_basic_payment_order.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $payment_order) {
            gritter_error('Failed, please select payment order', 'false');
            return redirect()->back();
        }

        if (! $payment_order->supplier->email) {
            gritter_error('Failed, please add email for supplier', 'false');
            return redirect()->back();
        }

        $data = array(
            'payment_order' => $payment_order, 
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'PURCHASE PAYMENT ORDER '. $payment_order->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $payment_order, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-purchasing::emails.purchasing.point.external.basic-payment-order', $data, function ($message) use ($payment_order, $warehouse, $data, $name) {
                $message->to($payment_order->supplier->email)->subject($name);
                $pdf = \PDF::loadView('point-purchasing::emails.purchasing.point.external.basic-payment-order-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email payment order', 'false');
        return redirect()->back();
    }
}

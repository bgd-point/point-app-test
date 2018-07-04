<?php

namespace Point\PointSales\Http\Controllers\Service;

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
use Point\Framework\Models\EmailHistory;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\Framework\Models\SettingJournal;
use Point\PointFinance\Models\PaymentReference;
use Point\PointSales\Helpers\ServicePaymentCollectionHelper;
use Point\PointSales\Models\Service\Downpayment;
use Point\PointSales\Models\Service\Invoice;
use Point\PointSales\Models\Service\PaymentCollection;
use Point\PointSales\Models\Service\PaymentCollectionDetail;
use Point\PointSales\Models\Service\PaymentCollectionOther;

class PaymentCollectionController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.service.payment.collection');

        $view = view('point-sales::app.sales.point.service.payment-collection.index');
        $list_payment_collection = PaymentCollection::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_payment_collection = ServicePaymentCollectionHelper::searchList($list_payment_collection, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_payment_collection = $list_payment_collection->paginate(100);
        $array_payment_collection_id = [];
        $view->array_payment_collection_id = $array_payment_collection_id;
        return $view;
    }
    public function ajaxDetailItem(Request $request, $id)
    {
        access_is_allowed('read.point.sales.service.payment.collection');
        $list_payment_detail = PaymentCollectionDetail::select('formulir.form_number',
            'point_sales_service_payment_collection_detail.detail_notes',
            'point_sales_service_payment_collection_detail.amount',
            'point_sales_service_payment_collection_id'
            )->
            joinFormulir()->joinPaymentCollection()->where(
                'point_sales_service_payment_collection_detail.point_sales_service_payment_collection_id', '=', $id
            );
        
        $list_payment_others  = PaymentCollectionOther::select('coa.name as form_number',
            'point_sales_service_payment_collection_other.other_notes',
            'point_sales_service_payment_collection_other.amount',
            'point_sales_service_payment_collection_id'
            )->
            joinCoa()->joinPaymentCollection()->where(
                'point_sales_service_payment_collection_other.point_sales_service_payment_collection_id', '=', $id
            );
        $results = $list_payment_detail->union($list_payment_others)->get();
        return response()->json($results);
    }
    public function indexPDF()
    {
        access_is_allowed('read.point.sales.service.payment.collection');
        $list_payment_collection = PaymentCollection::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        $list_payment_collection = ServicePaymentCollectionHelper::searchList($list_payment_collection, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.service.payment-collection.index-pdf', ['list_payment_collection' => $list_payment_collection]);
        
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-sales::app.sales.point.service.payment-collection.create-step-1');
        $view->list_invoice = Invoice::joinFormulir()
            ->availableToPaymentCollection()
            ->groupBy('person_id')
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep2($person_id)
    {
        $view = view('point-sales::app.sales.point.service.payment-collection.create-step-2');
        $view->person = Person::find($person_id);
        $view->list_downpayment = Downpayment::availableToCreatePaymentCollection($person_id)->get();
        $view->list_invoice = Invoice::joinFormulir()->joinPerson()->availableToCreatePaymentCollection($person_id)->selectOriginal()->get();
        $view->list_item = Item::active()->get();
        $view->list_coa = Coa::getNonSubledger();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::active()->get();
        return $view;
    }

    public function createStep3(Request $request)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
        ]);

        $view = view('point-sales::app.sales.point.service.payment-collection.create-step-3');
        $view->payment_type = app('request')->input('payment_type');
        $view->list_invoice = Invoice::whereIn('formulir_id', \Input::get('invoice_id'))->get();
        $view->invoice_rid = \Input::get('invoice_rid');
        $view->invoice_id = \Input::get('invoice_id');
        $view->amount_invoice = number_format_db(\Input::get('amount_invoice'));
        $view->available_invoice = number_format_db(\Input::get('available_invoice'));
        $view->original_amount_invoice = number_format_db(\Input::get('original_amount_invoice'));

        $view->list_downpayment = Downpayment::whereIn('formulir_id', \Input::get('downpayment_id'))->get();
        $view->downpayment_rid = \Input::get('downpayment_rid');
        $view->downpayment_id = \Input::get('downpayment_id');
        $view->amount_downpayment = number_format_db(\Input::get('amount_downpayment'));
        $view->available_downpayment = number_format_db(\Input::get('available_downpayment'));
        $view->original_amount_downpayment = number_format_db(\Input::get('original_amount_downpayment'));

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->person = Person::find(\Input::get('person_id'));
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->other_notes = \Input::get('other_notes');
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
            array_push($references_account, SettingJournal::where('group', 'point sales service')->where('name', 'account receivable')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }
        
        $downpayment_id = $request->input('downpayment_id');
        for ($i=0;$i < count($downpayment_id);$i++) {
            $reference_type = get_class(new Downpayment);
            array_push($formulir_id, Downpayment::find($downpayment_id[$i])->formulir_id);
            array_push($references_id, $downpayment_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point sales service')->where('name', 'sales downpayment')->first()->coa_id);
            array_push($references_amount, $request->input('downpayment_amount')[$i]);
            array_push($references_amount_original, $request->input('downpayment_amount_original')[$i]);
            array_push($references_notes, $request->input('downpayment_notes')[$i]);
            array_push($references, $reference_type::find($downpayment_id[$i]));
        }

        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));
        FormulirHelper::isAllowedToCreate('create.point.sales.service.payment.collection', date_format_db($request->input('form_date'), $request->input('time')), $formulir_id);
        $formulir = FormulirHelper::create($request->input(), 'point-sales-service-payment-collection');
        $payment_collection = ServicePaymentCollectionHelper::create($request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes);
        timeline_publish('create.point.sales.service.payment.collection', 'added new payment collection '  . $payment_collection->formulir->form_number);

        DB::commit();

        gritter_success('create form success', false);
        return redirect('sales/point/service/payment-collection/'.$payment_collection->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-sales::app.sales.point.service.payment-collection.show');
        $view->payment_collection = PaymentCollection::find($id);
        $view->list_payment_collection_archived = PaymentCollection::joinFormulir()->archived($view->payment_collection->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_payment_collection_archived->count();
        $view->email_history = EmailHistory::where('formulir_id', $view->payment_collection->formulir_id)->get();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-sales::app.sales.point.service.payment-collection.archived');
        $view->payment_collection_archived = PaymentCollection::find($id);
        $view->payment_collection = PaymentCollection::joinFormulir()->notArchived($view->payment_collection_archived->formulir->archived)->selectOriginal()->first();
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
        $payment_collection = PaymentCollection::find($id);
        $invoice_edit = ReferHelper::getRefersId(get_class(new Invoice), get_class($payment_collection), $payment_collection->id);
        $downpayment_edit = ReferHelper::getRefersId(get_class(new Downpayment), get_class($payment_collection), $payment_collection->id);
        $view = view('point-sales::app.sales.point.service.payment-collection.edit');
        $view->payment_collection = $payment_collection;
        $view->person = $payment_collection->person;
        $view->list_invoice = Invoice::joinFormulir()
            ->joinPerson()
            ->notArchived()
            ->availableToEditPaymentCollection($payment_collection->person_id, $invoice_edit)
            ->selectOriginal()
            ->get();
        $view->list_downpayment = Downpayment::joinFormulir()
            ->joinPerson()
            ->notArchived()
            ->availableToEditPaymentCollection($payment_collection->person_id, $downpayment_edit)
            ->selectOriginal()
            ->get();
        $view->payment_reference = PaymentReference::where('payment_reference_id', '=', $payment_collection->formulir_id)->first();
        $view->list_item = Item::active()->get();
        $view->list_coa = Coa::getNonSubledger();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::active()->get();
        return $view;
    }

    public function editReview(Request $request, $id)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'approval_to' => 'required',
        ]);

        $view = view('point-sales::app.sales.point.service.payment-collection.edit-review');
        $view->payment_collection = PaymentCollection::find($id);
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

        $view->payment_date = date_format_db(\Input::get('payment_date'), \Input::get('time'));
        $view->approval_to = User::find(\Input::get('approval_to'));
        $view->person = Person::find(\Input::get('person_id'));
        $view->coa_id = \Input::get('coa_id');
        $view->coa_amount = \Input::get('coa_amount');
        $view->other_notes = \Input::get('other_notes');
        $view->total = number_format_db(\Input::get('total'));
        $view->payment_type = \Input::get('payment_type');
        $view->allocation_id = \Input::get('allocation_id');

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
        $references_account = [];
        $references_notes = [];
        $formulir_id = [];
        $invoice_id = $request->input('invoice_id');
        for ($i=0;$i < count($invoice_id);$i++) {
            $reference_type = get_class(new Invoice);
            array_push($formulir_id, Invoice::find($invoice_id[$i])->formulir_id);
            array_push($references_id, $invoice_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point sales service')->where('name', 'account receivable')->first()->coa_id);
            array_push($references_amount, $request->input('invoice_amount')[$i]);
            array_push($references_amount_original, $request->input('invoice_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('invoice_amount_edit')[$i]);
            array_push($references_notes, $request->input('invoice_notes')[$i]);
            array_push($references, $reference_type::find($invoice_id[$i]));
        }

        $downpayment_id = $request->input('downpayment_id');
        for ($i=0;$i < count($downpayment_id);$i++) {
            $reference_type = get_class(new Downpayment);
            array_push($formulir_id, Downpayment::find($downpayment_id[$i])->formulir_id);
            array_push($references_id, $downpayment_id[$i]);
            array_push($references_type, $reference_type);
            array_push($references_account, SettingJournal::where('group', 'point sales service')->where('name', 'sales downpayment')->first()->coa_id);
            array_push($references_amount, $request->input('downpayment_amount')[$i]);
            array_push($references_amount_original, $request->input('downpayment_amount_original')[$i]);
            array_push($references_amount_edit, $request->input('downpayment_amount_edit')[$i]);
            array_push($references_notes, $request->input('downpayment_notes')[$i]);
            array_push($references, $reference_type::find($downpayment_id[$i]));
        }
        $request['form_date'] = date('Y-m-d', strtotime($request->input('payment_date')));

        $payment_collection = PaymentCollection::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.sales.service.payment.collection', date_format_db($request->input('form_date'), $request->input('time')), $payment_collection->formulir);
        PaymentReference::where('payment_reference_id', $payment_collection->formulir_id)->delete();

        $formulir_old = FormulirHelper::archive($request->input(), $payment_collection->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $payment_collection = ServicePaymentCollectionHelper::create($request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes, $references_amount_edit);
        timeline_publish('update.payment.collection', 'added new payment collection '  . $payment_collection->formulir->form_number);

        DB::commit();

        gritter_success('update form success', false);
        return redirect('sales/point/service/payment-collection/'.$payment_collection->id);
    }

    public function cancel()
    {
        $permission_slug = app('request')->input('permission_slug');
        $formulir_id = app('request')->input('formulir_id');

        DB::beginTransaction();
        
        $formulir = Formulir::find($formulir_id);
        FormulirHelper::isAllowedToCancel($permission_slug, $formulir);
        $formulir->form_status = -1;
        $formulir->canceled_at = date('Y-m-d H:i:s');
        $formulir->canceled_by = auth()->user()->id;
        $formulir->save();

        self::unlock($formulir->id);
        ReferHelper::cancel($formulir->formulirable_type, $formulir->formulirable_id);
        InventoryHelper::remove($formulir->id);
        JournalHelper::remove($formulir->id);
        AccountPayableAndReceivableHelper::remove($formulir->id);
        AllocationHelper::remove($formulir->id);
        FormulirHelper::cancelPaymentReference($formulir->id);


        DB::commit();

        return array('status' => 'success');
    }

    public static function unlock($locking_id)
    {
        $list_formulir_lock = FormulirLock::where('locking_id', '=', $locking_id)->get();
        foreach ($list_formulir_lock as $formulir_lock) {
            $locked_form = Formulir::find($formulir_lock->locked_id);
            if ($locked_form->formulirable_type != get_class(new Downpayment)) {
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
        $id = app('request')->input('payment_collection_id');
        $payment_collection = PaymentCollection::joinPerson()->where('point_sales_service_payment_collection.id', $id)->select('point_sales_service_payment_collection.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $payment_collection) {
            gritter_error('Failed, please select sales payment order', 'false');
            return redirect()->back();
        }

        if (! $payment_collection->person->email) {
            gritter_error('Failed, please add email for customer', 'false');
            return redirect()->back();
        }

        $data = array(
            'payment_collection' => $payment_collection,
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'SALES SERVICE PAYMENT COLLECTION FROM '. strtoupper($warehouse->store_name);

        \Queue::push(function ($job) use ($data, $request, $payment_collection, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.external.service-payment-collection', $data, function ($message) use ($payment_collection, $warehouse, $data, $name) {
                $message->to($payment_collection->person->email)->subject($name);
                $pdf = \PDF::loadView('point-sales::app.emails.sales.point.external.service-payment-collection-pdf', $data);
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email payment collection', 'false');

        $email_history = new EmailHistory;
        $email_history->sender = auth()->id();
        $email_history->recipient = $payment_collection->person_id;
        $email_history->recipient_email = $payment_collection->person->email;
        $email_history->formulir_id = $payment_collection->formulir_id;
        $email_history->sent_at = \Carbon\Carbon::now()->toDateTimeString();
        $email_history->save();
        
        return redirect()->back();
    }
}

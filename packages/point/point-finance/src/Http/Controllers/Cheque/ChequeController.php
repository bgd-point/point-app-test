<?php

namespace Point\PointFinance\Http\Controllers\Cheque;

use Illuminate\Auth\id;
use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\MasterBank;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Helpers\ChequeHelper;
use Point\PointFinance\Models\Cheque\Cheque;
use Point\PointFinance\Models\Cheque\ChequeDetail;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;

class ChequeController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.finance.cashier.cheque');

        $view = view('point-finance::app.finance.point.cheque.index');
        $view->list_cheque = Cheque::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        
        if (\Input::has('order_by')) {
            $view->list_cheque = $view->list_cheque->orderBy(\Input::get('order_by'), \Input::get('order_type'));
        } else {
            $view->list_cheque = $view->list_cheque->orderByStandard();
        }

        if (\Input::get('status') != 'all') {
            $view->list_cheque = $view->list_cheque->where('formulir.form_status', '=', \Input::get('status') ?: 0);
        }

        if (\Input::has('date_from')) {
            $view->list_cheque = $view->list_cheque->where('form_date', '>=', \DateHelper::formatDB(\Input::get('date_from'), 'start'));
        }

        if (\Input::has('date_to')) {
            $view->list_cheque = $view->list_cheque->where('form_date', '<=', \DateHelper::formatDB(\Input::get('date_to'), 'end'));
        }

        if (\Input::has('search')) {
            $view->list_cheque = $view->list_cheque->where(function ($q) {
                $q->where('formulir.notes', 'like', '%'.\Input::get('search').'%')
                   ->orWhere('formulir.form_number', 'like', '%'.\Input::get('search').'%');
            });
        }

        $view->list_cheque = $view->list_cheque->paginate(100);

        return $view;
    }

    public function printCheque(Request $request, $id)
    {
        $view = view('point-finance::app.finance.point.cheque.print');
        $view->cheque = Cheque::find($id);
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $view->warehouse_profiles = Warehouse::find($warehouse_id);
        } else {
            $view->warehouse_profiles = Warehouse::first();
        }
        if (!$view->warehouse_profiles) {
            throw new PointException('Please create your warehouse first to set your default name, address and phone number');
        }
        $view->project_name = $request->get('project')->name;
        return $view;
    }

    public function listCheque()
    {
        $view = view('point-finance::app.finance.point.cheque.list-cheque');
        $view->list_cheque_detail = ChequeDetail::searchList(\Input::get('status'))->paginate(100);

        return $view;
    }

    public function disbursement()
    {
        $view = view('point-finance::app.finance.point.cheque.disbursement');
        $id = explode(',', \Input::get('id'));
        $view->list_cheque_detail = ChequeDetail::whereIn('id', $id)->get();
        $view->list_coa = Coa::whereIn('coa_category_id', [1,2])->active()->get();

        return $view;
    }

    public function reject()
    {
        $view = view('point-finance::app.finance.point.cheque.reject');
        $id = explode(',', \Input::get('id'));
        $view->list_cheque_detail = ChequeDetail::whereIn('id', $id)->get();

        return $view;
    }

    public function action($id)
    {
        $view = view('point-finance::app.finance.point.cheque.action');
        $view->cheque_detail = ChequeDetail::find($id);

        return $view;
    }

    public function createNewCheque($id)
    {
        $view = view('point-finance::app.finance.point.cheque.create-new-cheque');
        $view->cheque_detail = ChequeDetail::find($id);
        $view->list_bank = MasterBank::all();
        if ($view->cheque_detail->status == 2) {
            gritter_error('Failed! You can not make payment with this transaction because it is already in process, please check your vesa.');
            return redirect('finance/point/cheque/list');
        }
        
        return $view;
    }

    public function createNewCashbank(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        \DB::beginTransaction();

        $type = \Input::get('type');
        $id = \Input::get('id');

        $cheque_detail = ChequeDetail::find($id);
        if ($cheque_detail->status == 2) {
            return response()->json(
                array(
                    'status' => 'error',
                    'message' => '<div class="alert alert-warning"><strong>Failed!</strong> You can not make payment with this transaction because it is already in process, please check your vesa.</div>'
                )
            );
        }

        $cheque_detail->status = 2;
        $cheque_detail->save();

        $cheque = $cheque_detail->cheque;

        $account_payable_receivable = AccountPayableAndReceivable::where('formulir_reference_id', $cheque_detail->rejected_formulir_id)->first();
        $rejected_formulir_id = $cheque->formulir_id;
        if ($account_payable_receivable) {
            $rejected_formulir_id = $cheque_detail->rejected_formulir_id;
        }

        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $rejected_formulir_id;
        $payment_reference->person_id = $cheque->person_id;
        $payment_reference->payment_flow = $cheque->payment_flow;
        $payment_reference->payment_type = $type;
        $payment_reference->total = $cheque_detail->amount;
        $payment_reference->save();

        $payment_reference_detail = new PaymentReferenceDetail;
        $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
        $payment_reference_detail->coa_id = $cheque->coa_id;
        $payment_reference_detail->allocation_id = 1;
        $payment_reference_detail->notes_detail = $cheque_detail->notes;
        $payment_reference_detail->amount = $cheque_detail->amount;
        $payment_reference_detail->form_reference_id = $rejected_formulir_id;
        $payment_reference_detail->subledger_id = $cheque->person_id;
        $payment_reference_detail->subledger_type = get_class(new Person);
        $payment_reference_detail->reference_id;
        $payment_reference_detail->reference_type;
        $payment_reference_detail->save();

        \DB::commit();

        return response()->json(
            array(
                'status' => 'success',
                'message' => '<div class="alert alert-info"><strong>Success!</strong> please check your vesa.</div>'
            )
        );
    }

    public function createNewStore()
    {
        $cheque = ChequeDetail::find(app('request')->input('reference_detail_id'));
        $cheque->status = 2;
        $cheque->save();

        \DB::beginTransaction();
        $amount = 0;
        for ($i=0 ; $i<count(app('request')->input('bank')); $i++) {
            $cheque_detail = new ChequeDetail;
            $cheque_detail->point_finance_cheque_id = app('request')->input('reference_id');
            $cheque_detail->bank = app('request')->input('bank')[$i];
            $cheque_detail->due_date = date_format_db(app('request')->input('due_date_cheque')[$i]);
            $cheque_detail->number = app('request')->input('number_cheque')[$i];
            $cheque_detail->notes = app('request')->input('notes_cheque')[$i];
            $cheque_detail->amount = number_format_db(app('request')->input('amount_cheque')[$i]);
            $cheque_detail->save();
            $amount += $cheque_detail->amount;
        }

        if ($amount != $cheque->amount) {
            throw new PointException('TOTAL CHEQUE MUST BALANCE WTIH REFERENCE');
        }

        \DB::commit();

        return redirect('finance/point/cheque/list');
    }

    public function disbursementProcess(Request $request)
    {
        $id_cheque = explode(',', \Input::get('id'));

        \DB::beginTransaction();
        foreach ($id_cheque as $id) {
            $cheque_detail = ChequeDetail::find($id);
            $cheque_detail->disbursement_at = date_format_db(\Input::get('disbursement_at'), \Input::get('time'));
            $cheque_detail->rejected_at = null;
            $cheque_detail->notes = \Input::get('cheque_notes');
            $cheque_detail->status = 1;
            $cheque_detail->save();

            $cheque = $cheque_detail->cheque;
            $form_number = FormulirHelper::number('point-finance-cheque-disbursement', $cheque->formulir->form_date);
            $formulir = new Formulir;
            $formulir->form_date = $cheque->formulir->form_date;
            $formulir->form_number = $form_number['form_number'];
            $formulir->form_raw_number = $form_number['raw'];
            $formulir->approval_to = 1;
            $formulir->approval_status = 1;
            $formulir->form_status = 0;
            $formulir->created_by = auth()->user()->id;
            $formulir->updated_by = auth()->user()->id;
            $formulir->save();

            self::journal($cheque_detail, $request, $formulir);
            ChequeHelper::close($cheque->formulir_id);
        }
        \DB::commit();

        return redirect('finance/point/cheque/list');
    }

    public function rejectProcess(Request $request)
    {
        $id_cheque = explode(',', \Input::get('id'));

        \DB::beginTransaction();
        foreach ($id_cheque as $id) {
            $cheque_detail = ChequeDetail::find($id);
            $cheque_detail->disbursement_at = null;
            $cheque_detail->rejected_at = date_format_db(\Input::get('rejected_at'), \Input::get('time'));
            $cheque_detail->notes = \Input::get('reject_notes');
            $cheque_detail->rejected_counter = $cheque_detail->rejected_counter + 1;
            $cheque_detail->save();

            if ($cheque_detail->rejected_counter > 3) {
                throw new PointException("CHEQUE/WESEL MORE THAN 3 TIMES IN REJECT");
            }

            $cheque = $cheque_detail->cheque;
            $form_number = FormulirHelper::number('point-finance-cheque-reject', $cheque->formulir->form_date);

            $formulir = new Formulir;
            $formulir->form_date = $cheque->formulir->form_date;
            $formulir->form_number = $form_number['form_number'];
            $formulir->form_raw_number = $form_number['raw'];
            $formulir->approval_to = 1;
            $formulir->approval_status = 1;
            $formulir->form_status = 0;
            $formulir->created_by = auth()->user()->id;
            $formulir->updated_by = auth()->user()->id;
            $formulir->save();

            ChequeHelper::open($cheque->formulir_id);
            if ($cheque_detail->disbursement_coa_id) {
                self::rejectJournal($cheque_detail, $request, $formulir);
            }

            $cheque_detail->rejected_formulir_id = $formulir->id;
            $cheque_detail->disbursement_coa_id = null;
            $cheque_detail->status = -1;
            $cheque_detail->save();

        }
        \DB::commit();

        return redirect('finance/point/cheque/list');
    }

    public static function journal($cheque_detail, $request, $formulir)
    {
        // CHEQUE
        $cheque = $cheque_detail->cheque;
        $account_payable_receivable = AccountPayableAndReceivable::where('reference_id', $cheque->id)->where('reference_type', get_class($cheque))->where('done', 0)->first();

        
        $position = JournalHelper::position($cheque->coa_id);
        $journal = new Journal();
        $journal->form_date = $cheque->formulir->form_date;
        $journal->coa_id = $cheque->coa_id;
        $journal->description = $cheque_detail->notes ?: '';
        $journal->$position = $cheque_detail->amount * -1;
        $journal->form_journal_id = $formulir->id;
        $journal->form_reference_id = $account_payable_receivable ? $account_payable_receivable->formulir_reference_id : $cheque->formulir->id;
        $journal->subledger_id = $cheque->person_id;
        $journal->subledger_type = get_class(new Person());
        $journal->save();

        if ($journal->debit > 0) {
            $position = 'credit';
        } else {
            $position = 'debit';
        }

        // BANK
        $journal = new Journal();
        $journal->form_date = $cheque->formulir->form_date;
        $journal->coa_id = $request->input('coa_id');
        $journal->description = $cheque_detail->notes ?: '';
        $journal->$position = $cheque_detail->amount;
        $journal->form_journal_id = $formulir->id;
        $journal->form_reference_id = $cheque->formulir->id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        $cheque_detail->disbursement_coa_id = $journal->coa_id;
        $cheque_detail->save();
    }

    public static function rejectJournal($cheque_detail, $request, $formulir)
    {
        // CHEQUE
        $cheque = $cheque_detail->cheque;

        $position = JournalHelper::position($cheque->coa_id);
        $journal = new Journal();
        $journal->form_date = $cheque->formulir->form_date;
        $journal->coa_id = $cheque->coa_id;
        $journal->description = $cheque_detail->notes ?: '';
        $journal->$position = $cheque_detail->amount;
        $journal->form_journal_id = $formulir->id;
        $journal->form_reference_id = $cheque->formulir->id;
        $journal->subledger_id = $cheque->person_id;
        $journal->subledger_type = get_class(new Person());
        $journal->save([
                'reference_id' => $cheque->id,
                'reference_type' => get_class($cheque)
            ]);

        // BANK
        $journal = new Journal();
        $journal->form_date = $cheque->formulir->form_date;
        $journal->coa_id = $cheque_detail->disbursement_coa_id;
        $journal->description = $cheque_detail->notes ?: '';
        $journal->$position = $cheque_detail->amount * -1;
        $journal->form_journal_id = $formulir->id;
        $journal->form_reference_id = $cheque->formulir->id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();

        JournalHelper::checkJournalBalance($cheque->formulir_id);
    }
}

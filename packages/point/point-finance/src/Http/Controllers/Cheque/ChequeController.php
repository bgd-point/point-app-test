<?php

namespace Point\PointFinance\Http\Controllers\Cheque;

use Illuminate\Auth\id;
use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Models\Cheque\Cheque;
use Point\PointFinance\Models\Cheque\ChequeDetail;

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

    public function pendingCheque()
    {
        $view = view('point-finance::app.finance.point.cheque.pending-cheque');
        $view->list_cheque_detail = ChequeDetail::joinCheque()->joinFormulir()->where('formulir.form_status', 1)->whereNull('formulir.archived')->where('point_finance_cheque_detail.status', 0)->select('point_finance_cheque_detail.*')->paginate(100);

        return $view;
    }

    public function liquid()
    {
        $view = view('point-finance::app.finance.point.cheque.liquid');
        $id = explode(',', \Input::get('id'));
        $view->list_cheque_detail = ChequeDetail::whereIn('id', $id)->get();
        $view->list_coa = Coa::whereIn('coa_category_id', [1,2])->active()->get();

        return $view;
    }

    public function liquidProcess(Request $request)
    {
        $id_cheque = explode(',', \Input::get('id'));

        \DB::beginTransaction();
        foreach ($id_cheque as $id) {
            $cheque_detail = ChequeDetail::find($id);
            $cheque_detail->liquid_date = date_format_db(\Input::get('liquid_date'), \Input::get('time'));
            $cheque_detail->status = 1;
            $cheque_detail->save();

            self::journal($cheque_detail, $request);
        }
        \DB::commit();

        return redirect('finance/point/cheque');
    }

    public static function journal($cheque_detail, $request)
    {
        // CHEQUE
        $cheque = $cheque_detail->cheque;

        $position = JournalHelper::position($cheque->coa_id);
        $journal = new Journal();
        $journal->form_date = $cheque->formulir->form_date;
        $journal->coa_id = $cheque->coa_id;
        $journal->description = $cheque_detail->notes ?: '';
        $journal->$position = $cheque_detail->amount * -1;
        $journal->form_journal_id = $cheque->formulir_id;
        $journal->form_reference_id = $cheque->formulir_id;
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
        $journal->form_journal_id = $cheque->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id;
        $journal->subledger_type;
        $journal->save();
    }
}

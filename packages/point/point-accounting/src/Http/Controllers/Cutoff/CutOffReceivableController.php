<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Warehouse;
use Point\PointAccounting\Helpers\CutOffHelper;
use Point\PointAccounting\Helpers\CutOffReceivableHelper;
use Point\PointAccounting\Http\Requests\CutOffSubledgerRequest;
use Point\PointAccounting\Models\CutOffReceivable;
use Point\PointAccounting\Models\CutOffReceivableDetail;

class CutOffReceivableController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        access_is_allowed('read.point.accounting.cut.off.receivable');

        $view = view('point-accounting::app.accounting.point.cut-off.receivable.index');
        $view->list_cut_off = CutOffReceivable::joinFormulir()
            ->notArchived()
            ->selectOriginal()
            ->orderByStandard()
            ->groupBy('formulir_id')
            ->paginate(100);
        return $view;
    }

    public function create()
    {
        access_is_allowed('create.point.accounting.cut.off.receivable');
        
        $view = view('point-accounting::app.accounting.point.cut-off.receivable.create');
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Account Receivable')->selectOriginal()->orderBy('coa_number')->orderBy('name')->get(); // get all coa where category receivable "Account Receivable"
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
    }

    public function store(CutOffSubledgerRequest $request)
    {
        formulir_is_allowed_to_create('create.point.accounting.cut.off.receivable', date_format_db($request->input('form_date')), []);
        
        \DB::beginTransaction();

        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffReceivable()));
        $formulir = formulir_create($request->input(), 'point-accounting-cut-off-receivable');
        $cut_off_receivable = CutOffReceivableHelper::create($formulir);
        timeline_publish('create.point.accounting.cut.off.receivable', 'user '.\Auth::user()->name.' successfully create cut off receivable ' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.receivable', auth()->user()->id);
        gritter_success('Form cut off receivable "'. $formulir->form_number .'" Success to add');
        return redirect('accounting/point/cut-off/receivable/'.$cut_off_receivable->id);
    }

    public function show($id)
    {
        access_is_allowed('read.point.accounting.cut.off.receivable');
        $view = view('point-accounting::app.accounting.point.cut-off.receivable.show');
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Account Receivable')->selectOriginal()->orderBy('coa_number')->orderBy('name')->get();
        $view->cut_off_receivable = CutOffReceivable::find($id);
        $view->list_cut_off_receivable_archived = CutOffReceivable::joinFormulir()->archived($view->cut_off_receivable->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cut_off_receivable_archived->count();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.accounting.cut.off.receivable');
        $view = view('point-accounting::app.accounting.point.cut-off.receivable.archived');
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Account Receivable')->selectOriginal()->orderBy('coa_number')->orderBy('name')->get();
        $view->cut_off_receivable_archived = CutOffReceivable::find($id);

        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.accounting.cut.off.receivable');

        $view = view('point-accounting::app.accounting.point.cut-off.receivable.edit');
        $view->cut_off_receivable = CutOffReceivable::find($id);
        self::restoreToTemp($view->cut_off_receivable);
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Account Receivable')->selectOriginal()->orderBy('coa_number')->orderBy('name')->get();
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
    }

    public function update(CutOffSubledgerRequest $request, $id)
    {
        $formulir_check = Formulir::find($id);
        
        formulir_is_allowed_to_update('update.point.accounting.cut.off.receivable', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);

        \DB::beginTransaction();

        $request['formulir_id'] = $formulir_check->id;
        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffReceivable()));
        CutOffHelper::cancel($id);
        $formulir_old = FormulirHelper::archive($request->input(), $id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $cut_off = CutOffReceivableHelper::create($formulir);
        timeline_publish('update.point.accounting.cut.off.receivable', 'user '.\Auth::user()->name.' successfully update cut off receivable' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.receivable', auth()->user()->id);
        gritter_success('Form cut off receivable "'. $formulir->form_number .'" Success to update');
        return redirect('accounting/point/cut-off/receivable/'.$cut_off->id);
    }

    public function cancel()
    {
        $permission_slug = app('request')->input('permission_slug');
        $formulir_id = app('request')->input('formulir_id');

        DB::beginTransaction();

        FormulirHelper::cancel($permission_slug, $formulir_id);
        CutOffHelper::cancel($formulir_id);

        DB::commit();

        return array('status' => 'success');
    }
    
    public function _loadDetails()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa = Coa::find(\Input::get('coa_id'));
        $view = view('point-accounting::app.accounting.point.cut-off.receivable._form-details');

        $view->class = $coa->subledger_type;
        $view->list_master = $coa->subledger_type::active()->get();
        $view->coa_id = $coa->id;
        $view->list_warehouse = Warehouse::all();
        $temp = TempDataHelper::getAllRowHaveKeyValue('cut.off.receivable', auth()->user()->id, 'coa_id', $coa->id);
        if (!$temp) {
            self::restoreTempDefault($coa->id);
        }
        $view->details = TempDataHelper::getAllRowHaveKeyValue('cut.off.receivable', auth()->user()->id, 'coa_id', $coa->id);
        return $view;
    }

    public function _loadDetailsAccountReceivable()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa = Coa::find(\Input::get('coa_id'));
        $view = view('point-accounting::app.accounting.point.cut-off.receivable._details-account-receivable');
        $view->details = CutOffReceivableDetail::where('cut_off_receivable_id', \Input::get('cut_off_id'))->where('coa_id', \Input::get('coa_id'))->get();
        $view->coa = $coa;
        
        return $view;
    }

    public function _storeTmp()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $total = 0;
        $coa_id = \Input::get('modal_coa_id');
        $position = JournalHelper::position($coa_id);
        // remove temp axist
        TempDataHelper::removeRowHaveKeyValue('cut.off.receivable', auth()->user()->id, 'coa_id', $coa_id);

        for ($i=0; $i < count(\Input::get('subledger_id')); $i++) {
            $keys = [
                'coa_id'=> $coa_id,
                'position' => $position,
                'type'=> 'Point\Framework\Models\Master\Person',
                'subledger_id'=> \Input::get('subledger_id')[$i],
                'notes'=> \Input::get('notes')[$i],
                'amount'=> number_format_db(\Input::get('amount')[$i])
            ];

            self::storeTempReceivable($keys);
            $total += number_format_db(\Input::get('amount')[$i]);
        }
        
        $response = array('status' => 'success', 'total' => $total, 'position' => $position );
        return response()->json($response);
    }

    public function restoreToTemp($cut_off_receivable)
    {
        $check_coa = TempDataHelper::get('cut.off.receivable', auth()->user()->id);
        if (count($check_coa) != null) {
            return false;
        }

        TempDataHelper::clear('cut.off.receivable', auth()->user()->id);
        foreach ($cut_off_receivable->cutOffReceivableDetail as $account) {
            $keys = [
                'coa_id'=> $account->coa_id,
                'type'=> 'Point\Framework\Models\Master\Person',
                'subledger_id'=> $account->subledger_id,
                'notes'=> $account->notes,
                'amount'=> number_format_db($account->amount),
            ];

            if ($account->subledger_id) {
                self::storeTempReceivable($keys);
            }
        }
    }

    public function restoreTempDefault($coa_id)
    {
        $check_coa = TempDataHelper::get('cut.off.receivable', auth()->user()->id);
        if (count($check_coa) != null) {
            return false;
        }

        TempDataHelper::clear('cut.off.receivable', auth()->user()->id);
        $value_of_account_receivable = AccountPayableAndReceivable::where('account_id', $coa_id)->where('done', 0)->groupBy('formulir_reference_id')->get();
        if (!$value_of_account_receivable) {
            return false;
        }

        $formulir_reference_id = [];
        foreach ($value_of_account_receivable as $account_receivable) {
            array_push($formulir_reference_id, $account_receivable->formulir_reference_id);
        }

        $position = JournalHelper::position($coa_id);
        $journal = Journal::where('coa_id', $coa_id)->whereIn('form_journal_id', $formulir_reference_id)->get();
            
        foreach ($journal as $account) {
            $keys = [
                'coa_id'=> $account->coa_id,
                'type'=> $account->subledger_type,
                'subledger_id'=> $account->subledger_id,
                'notes'=> $account->description,
                'amount'=> number_format_db($account->$position),
            ];

            self::storeTempReceivable($keys);
        }
    }

    public function storeTempReceivable($keys)
    {
        $temp = new Temp;
        $temp->name = 'cut.off.receivable';
        $temp->user_id = auth()->user()->id;
        $temp->keys = serialize($keys);
        $temp->save();
    }

    public function _deleteTmp()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        TempDataHelper::remove(\Input::get('id'));
        $response = array('status' => 'success');
        return response()->json($response);
    }

    public function _clearTmpDetail()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $position = JournalHelper::position(\Input::get('modal_coa_id'));
        TempDataHelper::removeRowHaveKeyValue('cut.off.receivable', auth()->user()->id, 'coa_id', \Input::get('modal_coa_id'));
        $response = array('status' => 'success', 'position'=>$position);
        return response()->json($response);
    }

    public function clearTmp()
    {
        TempDataHelper::clear('cut.off.receivable', auth()->user()->id);
        gritter_success('Temporary cleared');
        return redirect('accounting/point/cut-off/receivable/create');
    }
}

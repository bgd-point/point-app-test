<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

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
use Point\PointAccounting\Helpers\CutOffPayableHelper;
use Point\PointAccounting\Http\Requests\CutOffSubledgerRequest;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffPayableDetail;

class CutOffPayableController extends Controller
{	
	use ValidationTrait;

	public function index()
	{
		access_is_allowed('read.point.accounting.cut.off.payable');

		$view = view('point-accounting::app.accounting.point.cut-off.payable.index');
       	$view->list_cut_off = CutOffPayable::joinFormulir()
            ->notArchived()
            ->selectOriginal()
            ->orderByStandard()
            ->groupBy('formulir_id')
            ->paginate(100);
        return $view;
	}

	public function create()
	{
		access_is_allowed('create.point.accounting.cut.off.payable');
		
		$view = view('point-accounting::app.accounting.point.cut-off.payable.create');
        $view->list_coa = Coa::getSubledgerPerson();
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function store(CutOffSubledgerRequest $request)
	{
		formulir_is_allowed_to_create('create.point.accounting.cut.off.payable', date_format_db($request->input('form_date')), []);
		
		\DB::beginTransaction();

        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffPayable()));
        $formulir = formulir_create($request->input(), 'point-accounting-cut-off-payable');
        $cut_off_payable = CutOffPayableHelper::create($formulir);
        timeline_publish('create.point.accounting.cut.off.payable','user '.\Auth::user()->name.' successfully create cut off payable ' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.payable', auth()->user()->id);
        gritter_success('Form cut off payable "'. $formulir->form_number .'" Success to add');
        return redirect('accounting/point/cut-off/payable/'.$cut_off_payable->id);
    }

	public function show($id)
	{
		access_is_allowed('read.point.accounting.cut.off.payable');
		$view = view('point-accounting::app.accounting.point.cut-off.payable.show');
        $view->list_coa = Coa::active()->where('coa_category_id', 8)->get();
        $view->cut_off_payable = CutOffPayable::find($id);
        $view->list_cut_off_payable_archived = CutOffPayable::joinFormulir()->archived($view->cut_off_payable->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cut_off_payable_archived->count();
        return $view;
	}

	public function archived($id)
	{
		access_is_allowed('read.point.accounting.cut.off.payable');
        $view = view('point-accounting::app.accounting.point.cut-off.payable.archived');
        $view->list_coa = Coa::active()->where('coa_category_id', 8)->get();
        $view->cut_off_payable_archived = CutOffPayable::find($id);

        return $view;
    }

	public function edit($id)
	{
		access_is_allowed('update.point.accounting.cut.off.payable');

		$view = view('point-accounting::app.accounting.point.cut-off.payable.edit');
		$view->cut_off_payable = CutOffPayable::find($id);
        self::restoreToTemp($view->cut_off_payable);
        $view->list_coa = Coa::getSubledgerPerson();
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function update(CutOffSubledgerRequest $request, $id)
	{  
        $formulir_check = Formulir::find($id);
        formulir_is_allowed_to_update('update.point.accounting.cut.off.payable', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);

        \DB::beginTransaction();

        $request['formulir_id'] = $formulir_check->id;
        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffPayable()));
        CutOffHelper::cancel($id);
        $formulir_old = FormulirHelper::archive($request->input(), $id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $cut_off = CutOffPayableHelper::create($formulir);
        timeline_publish('update.point.accounting.cut.off.payable','user '.\Auth::user()->name.' successfully update cut off payable' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.payable', auth()->user()->id);
        gritter_success('Form cut off payable "'. $formulir->form_number .'" Success to update');
        return redirect('accounting/point/cut-off/payable/'.$cut_off->id);
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
        $view = view('point-accounting::app.accounting.point.cut-off.payable._form-details');

        $view->class = $coa->subledger_type;
        $view->list_master = $coa->subledger_type::active()->get();
       	$view->coa_id = $coa->id;
        $view->list_warehouse = Warehouse::all();
       	$view->temp = TempDataHelper::getAllRowHaveKeyValue('cut.off.payable', auth()->user()->id, 'coa_id', $coa->id);
        if (!$view->temp) {
            self::restoreTempDefault($coa->id);
        }
        $view->details = TempDataHelper::getAllRowHaveKeyValue('cut.off.payable', auth()->user()->id, 'coa_id', $coa->id);
        return $view;

	}

	public function _loadDetailsAccountPayable()
	{
		if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa = Coa::find(\Input::get('coa_id'));
        $view = view('point-accounting::app.accounting.point.cut-off.payable._details-account-payable');
        $view->details = CutOffPayableDetail::where('cut_off_payable_id',\Input::get('cut_off_id'))->where('coa_id',\Input::get('coa_id'))->get();
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
        // remove temp axist
        TempDataHelper::removeRowHaveKeyValue('cut.off.payable', auth()->user()->id, 'coa_id', $coa_id);

        for ($i=0; $i < count(\Input::get('subledger_id')); $i++) { 
        	
            $keys = [
                'coa_id'=> $coa_id,
                'type'=> 'Point\Framework\Models\Master\Person',
                'subledger_id'=> \Input::get('subledger_id')[$i],
                'notes'=> \Input::get('notes')[$i],
                'amount'=> number_format_db(\Input::get('amount')[$i])
            ];

	        self::storeTempPayable($keys);	
            $total += number_format_db(\Input::get('amount')[$i]);
        }
        
        $response = array('status' => 'success', 'total' => $total);
        return response()->json($response);
	}

    public function restoreToTemp($cut_off_payable)
	{
        $check_coa = TempDataHelper::get('cut.off.payable', auth()->user()->id);
        if(count($check_coa) != null){
            return false;
        }

        TempDataHelper::clear('cut.off.payable', auth()->user()->id);
		foreach ($cut_off_payable->cutOffPayableDetail as $payable) {
            
            $keys = [
                'coa_id'=> $payable->coa_id,
                'type'=> 'Point\Framework\Models\Master\Person',
                'subledger_id'=> $payable->subledger_id,
                'notes'=> $payable->notes,
                'amount'=> number_format_db($payable->amount),
            ];

            if ($payable->subledger_id) {
                self::storeTempPayable($keys);    
            }
        }

	}

    public function restoreTempDefault($coa_id)
    {
        $check_coa = TempDataHelper::get('cut.off.payable', auth()->user()->id);
        if(count($check_coa) != null){
            return false;
        }

        TempDataHelper::clear('cut.off.payable', auth()->user()->id);
        $value_of_account_payable = AccountPayableAndReceivable::where('account_id', $coa_id)->where('done', 0)->groupBy('formulir_reference_id')->get();
        if (!$value_of_account_payable) {
            return false;
        }

        $formulir_reference_id = [];
        foreach ($value_of_account_payable as $account_payable) {
            array_push($formulir_reference_id, $account_payable->formulir_reference_id);
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

            self::storeTempPayable($keys);
        }

    }
    
    public function storeTempPayable($keys)
    {
        $temp = new Temp;
        $temp->name = 'cut.off.payable';
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
        TempDataHelper::removeRowHaveKeyValue('cut.off.payable', auth()->user()->id, 'coa_id', \Input::get('modal_coa_id'));
        $response = array('status' => 'success');
        return response()->json($response); 
    }

    public function clearTmp()
    {
        TempDataHelper::clear('cut.off.payable', auth()->user()->id);
        gritter_success('Temporary cleared');
        return redirect('accounting/point/cut-off/payable/create');    
    }
}

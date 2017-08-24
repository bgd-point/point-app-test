<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Coa;
use Point\PointAccounting\Helpers\CutOffAccountHelper;
use Point\PointAccounting\Helpers\CutOffHelper;
use Point\PointAccounting\Http\Requests\CutOffRequest;
use Point\PointAccounting\Models\CutOff;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffAccountDetail;
use Point\PointAccounting\Models\CutOffAccountSubledger;

class CutOffAccountController extends Controller
{	
	use ValidationTrait;

	public function index()
	{
		access_is_allowed('read.point.accounting.cut.off.account');

		$view = view('point-accounting::app.accounting.point.cut-off.account.index');
       	$view->list_cut_off = CutOffAccount::joinFormulir()
            ->notArchived()
            ->selectOriginal()
            ->orderByStandard()
            ->groupBy('formulir_id')
            ->paginate(100);

        return $view;
	}

	public function create()
	{
		access_is_allowed('create.point.accounting.cut.off.account');
		
		$view = view('point-accounting::app.accounting.point.cut-off.account.create');
        $view->list_coa = Coa::active()->orderBy('coa_number')->orderBy('name')->get();
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function store(CutOffRequest $request)
	{
		formulir_is_allowed_to_create('create.point.accounting.cut.off.account', date_format_db($request->input('form_date')), []);
		
		DB::beginTransaction();

        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffAccount()));
        $formulir = formulir_create($request->input(), 'point-accounting-cut-off-account');
        $cut_off_account = CutOffAccountHelper::create($formulir);
        timeline_publish('create.point.accounting.cut.off.account','user '.\Auth::user()->name.' successfully create cut off ' . $cut_off_account->formulir->form_number);

        DB::commit();

        TempDataHelper::clear('cut.off', auth()->user()->id);
        gritter_success('Form cut off account "'. $cut_off_account->formulir->form_number .'" Success to add');
        return redirect('accounting/point/cut-off/account/'.$cut_off_account->id);
    }

	public function show($id)
	{
		access_is_allowed('read.point.accounting.cut.off.account');
		$view = view('point-accounting::app.accounting.point.cut-off.account.show');
        $view->list_coa = Coa::active()->get();
        $view->cut_off_account = CutOffAccount::find($id);
        $view->list_cut_off_account_detail = CutOffAccountDetail::where('cut_off_account_id', $id);
        $view->list_cut_off_account_archived = CutOffAccount::joinFormulir()->archived($view->cut_off_account->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cut_off_account_archived->count();
        return $view;
	}

	public function archived($id)
	{
		access_is_allowed('read.point.accounting.cut.off.account');
        $view = view('point-accounting::app.accounting.point.cut-off.account.archived');
        $view->cut_off_account = CutOffAccount::find($id);
        $view->list_coa = Coa::active()->orderBy('coa_number')->orderBy('name')->get();
        $view->cut_off_account_archived = CutOffAccountDetail::where('cut_off_account_id', $id);
        
        return $view;
    }

	public function edit($id)
	{
		access_is_allowed('update.point.accounting.cut.off.account');

		$view = view('point-accounting::app.accounting.point.cut-off.account.edit');
		$view->cut_off_account = CutOffAccount::find($id);
        $check_coa = TempDataHelper::get('cut.off', auth()->user()->id);
        if(count($check_coa) == null){
            self::restoreToTemp($view->cut_off_account);
        }
        
        $view->list_coa = Coa::active()->orderBy('coa_number')->orderBy('name')->get();
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function update(CutOffRequest $request, $id)
	{  
        $formulir_check = Formulir::find($id);
        formulir_is_allowed_to_update('update.point.accounting.cut.off.account', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);
        
        DB::beginTransaction();

        $request['formulir_id'] = $formulir_check->id;
        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffAccount()));
        CutOffHelper::cancel($id);
        $formulir_old = FormulirHelper::archive($request->input(), $id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $cut_off_account = CutOffAccountHelper::create($formulir);
        timeline_publish('update.point.accounting.cut.off.account','user '.\Auth::user()->name.' successfully update cut off account' . $formulir->form_number);

        DB::commit();

        TempDataHelper::clear('cut.off', auth()->user()->id);
        gritter_success('Form cut off "'. $formulir->form_number .'" Success to update');
        return redirect('accounting/point/cut-off/account/'.$cut_off_account->id);
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
    
	public function _storeTmp()
	{
		if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa_id = \Input::get('coa_id');
		$amount = \Input::get('amount');
		$position = \Input::get('position');
        
        #check if axist
        $check_coa = TempDataHelper::getAllRowHaveKeyValue('cut.off', auth()->user()->id, 'coa_id', $coa_id);
        if($check_coa){
        	$temp = Temp::find($check_coa[0]['rowid']);
        }else{
        	$temp = new Temp;
        }
        
		$temp->name = 'cut.off';
        $temp->keys = serialize([
            'coa_id'=> $coa_id,
            'position'=> $position,
            'amount'=> $amount,
        ]);
        $temp->save();	

        $response = array('status' => 'success', 'total' => $amount, 'position' => $position );
        return response()->json($response);	
	}

	public function restoreToTemp($cut_off_account)
	{
        $cut_off_account_detail = CutOffAccountDetail::where('cut_off_account_id', $cut_off_account->id)->get();
        TempDataHelper::clear('cut.off', auth()->user()->id);
		foreach ($cut_off_account_detail as $account) {

            $position = 'credit';
            $amount = $account->credit;
			if($account->debit > 0){
				$position = 'debit';
                $amount = $account->debit;
			}

			$temp = new Temp;
        	$temp->name = 'cut.off';
	        $temp->keys = serialize([
	            'coa_id'=> $account->coa_id,
	            'position'=> $position,
	            'amount'=> $amount,
	        ]);
	        $temp->save();	
        }
    }
	
    public function clearTmp()
    {
        
        TempDataHelper::clear('cut.off', auth()->user()->id);
        gritter_success('Temporary cleared');
        return redirect('accounting/point/cut-off/account/create');    
    }
}

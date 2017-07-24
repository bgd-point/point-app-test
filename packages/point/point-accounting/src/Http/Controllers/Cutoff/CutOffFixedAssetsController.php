<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use Point\Core\Helpers\TempDataHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Coa;
use Point\PointAccounting\Helpers\CutOffFixedAssetsHelper;
use Point\PointAccounting\Helpers\CutOffHelper;
use Point\PointAccounting\Http\Requests\CutOffSubledgerRequest;
use Point\PointAccounting\Models\CutOffFixedAssets;
use Point\PointAccounting\Models\CutOffFixedAssetsDetail;

class CutOffFixedAssetsController extends Controller
{	
	use ValidationTrait;

	public function index()
	{
		access_is_allowed('read.point.accounting.cut.off.fixed.assets');

		$view = view('point-accounting::app.accounting.point.cut-off.fixed-assets.index');
       	$view->list_cut_off = CutOffFixedAssets::joinFormulir()
            ->notArchived()
            ->selectOriginal()
            ->orderByStandard()
            ->groupBy('formulir_id')
            ->paginate(100);
        return $view;
	}

	public function create()
	{
		access_is_allowed('create.point.accounting.cut.off.fixed.assets');
		
		$view = view('point-accounting::app.accounting.point.cut-off.fixed-assets.create');
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Fixed Assets')->where('has_subledger', 1)->selectOriginal()->get(); // get all coa where category fixed.assets
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function store(CutOffSubledgerRequest $request)
	{
		formulir_is_allowed_to_create('create.point.accounting.cut.off.fixed.assets', date_format_db($request->input('form_date')), []);
		
		\DB::beginTransaction();

        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffFixedAssets()));
        $formulir = formulir_create($request->input(), 'point-accounting-cut-off-fixed-assets');
        $cut_off_fixed_assets = CutOffFixedAssetsHelper::create($formulir);
        timeline_publish('create.point.accounting.cut.off.fixed.assets','user '.\Auth::user()->name.' successfully create cut off fixed assets ' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.fixed.assets', auth()->user()->id);
        gritter_success('Form cut off fixed assets "'. $formulir->form_number .'" Success to add');
        return redirect('accounting/point/cut-off/fixed-assets/'.$cut_off_fixed_assets->id);
    }

	public function show($id)
	{
		access_is_allowed('read.point.accounting.cut.off.fixed.assets');
		$view = view('point-accounting::app.accounting.point.cut-off.fixed-assets.show');
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Fixed Assets')->where('has_subledger', 1)->selectOriginal()->get();
        $view->cut_off_fixed_assets = CutOffFixedAssets::find($id);
        $view->list_cut_off_fixed_assets_archived = CutOffFixedAssets::joinFormulir()->archived($view->cut_off_fixed_assets->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cut_off_fixed_assets_archived->count();
        return $view;
	}

	public function archived($id)
	{
		access_is_allowed('read.point.accounting.cut.off.fixed.assets');
        $view = view('point-accounting::app.accounting.point.cut-off.fixed-assets.archived');
        $view->cut_off_fixed_assets_archived = CutOffFixedAssets::find($id);
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Fixed Assets')->where('has_subledger', 1)->selectOriginal()->get();

        return $view;
    }

	public function edit($id)
	{
		access_is_allowed('update.point.accounting.cut.off.fixed.assets');

		$view = view('point-accounting::app.accounting.point.cut-off.fixed-assets.edit');
		$view->cut_off_fixed_assets = CutOffFixedAssets::find($id);
        self::restoreToTemp($view->cut_off_fixed_assets);
        $view->list_coa = Coa::active()->joinCategory()->where('coa_category.name', 'Fixed Assets')->where('has_subledger', 1)->selectOriginal()->get();
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function update(CutOffSubledgerRequest $request, $id)
	{  
        $formulir_check = Formulir::find($id);
		
        formulir_is_allowed_to_update('update.point.accounting.cut.off.fixed.assets', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);

        \DB::beginTransaction();

        $request['formulir_id'] = $formulir_check->id;
        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffFixedAssets()));
        CutOffHelper::cancel($formulir_check->formulir_id);
        $formulir_old = FormulirHelper::archive($request->input(), $id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $cut_off = CutOffFixedAssetsHelper::create($formulir);
        timeline_publish('update.point.accounting.cut.off.fixed.assets','user '.\Auth::user()->name.' successfully update cut off fixed assets' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.fixed.assets', auth()->user()->id);
        gritter_success('Form cut off fixed assets "'. $formulir->form_number .'" Success to update');
        return redirect('accounting/point/cut-off/fixed-assets/'.$cut_off->id);
	}

	public function _loadDetails()
	{
		if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa = Coa::find(\Input::get('coa_id'));
        $view = view('point-accounting::app.accounting.point.cut-off.fixed-assets._form-details');

        $view->class = $coa->subledger_type;
       	$view->coa_id = $coa->id;
        $view->list_supplier = PersonHelper::getByType(['supplier']);
       	$view->details = TempDataHelper::getAllRowHaveKeyValue('cut.off.fixed.assets', auth()->user()->id, 'coa_id', $coa->id);
        return $view;

	}

	public function _loadDetailsAccountFixedAssets()
	{
		if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa = Coa::find(\Input::get('coa_id'));
        $view = view('point-accounting::app.accounting.point.cut-off.fixed-assets._details-account-fixed-assets');
        $view->details = CutOffFixedAssetsDetail::where('fixed_assets_id',\Input::get('cut_off_id'))->where('coa_id',\Input::get('coa_id'))->get();
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
        TempDataHelper::removeRowHaveKeyValue('cut.off.fixed.assets', auth()->user()->id, 'coa_id', $coa_id);

        for ($i=0; $i < count(\Input::get('supplier_id')); $i++) { 
        	
            // if input have column blank, skip it
            if(\Input::get('total_paid')[$i] == ''
                || \Input::get('quantity')[$i] == '' 
                || \Input::get('price')[$i] == '' 
                || \Input::get('total_price')[$i] == ''
                || \Input::get('supplier_id')[$i] == '' ) 
            {

                continue;
            }

            $keys = [
                'coa_id'=> $coa_id,
                'useful_life'=> number_format_db(\Input::get('useful_life')[$i]),
                'supplier_id'=> \Input::get('supplier_id')[$i],
                'date_purchased'=> \Input::get('date_purchased')[$i],
                'name_asset'=> \Input::get('name_asset')[$i],
                'country'=> \Input::get('country')[$i],
                'total_paid'=> number_format_db(\Input::get('total_paid')[$i]),
                'quantity'=> number_format_db(\Input::get('quantity')[$i]),
                'price'=> number_format_db(\Input::get('price')[$i]),
                'total_price'=> number_format_db(\Input::get('total_price')[$i]),
            ];

	        self::storeTempFixedAssets($keys);	
            $total += number_format_db(\Input::get('total_price')[$i]);
        }
        
        $response = array('status' => 'success', 'total' => $total);
        return response()->json($response);
	}

    public function restoreToTemp($cut_off_fixed_assets)
	{
        $check_coa = TempDataHelper::get('cut.off.fixed.assets', auth()->user()->id);
        if(count($check_coa) != null){
            return false;
        }
        TempDataHelper::clear('cut.off.fixed.assets', auth()->user()->id);

        $cut_off_fixed_assets_detail = CutOffFixedAssetsDetail::where('fixed_assets_id', $cut_off_fixed_assets->id)->get();
        foreach ($cut_off_fixed_assets_detail as $fixed_assets) {
            
            $keys = [
                'coa_id'=> $fixed_assets->coa_id,
                'useful_life'=> $fixed_assets->useful_life,
                'supplier_id'=> $fixed_assets->supplier_id,
                'date_purchased'=> $fixed_assets->date_purchased,
                'name_asset'=> $fixed_assets->name,
                'country'=> $fixed_assets->country,
                'total_paid'=> $fixed_assets->total_paid,
                'quantity'=> $fixed_assets->quantity,
                'price'=> $fixed_assets->price,
                'total_price'=> $fixed_assets->total_price,
            ];

            self::storeTempFixedAssets($keys);    
        }

	}

    public function storeTempFixedAssets($keys)
    {
        $temp = new Temp;
        $temp->name = 'cut.off.fixed.assets';
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
        TempDataHelper::removeRowHaveKeyValue('cut.off.fixed.assets', auth()->user()->id, 'coa_id', \Input::get('modal_coa_id'));
        $response = array('status' => 'success');
        return response()->json($response); 
    }

    public function clearTmp()
    {
        TempDataHelper::clear('cut.off.detail', auth()->user()->id);
        TempDataHelper::clear('cut.off', auth()->user()->id);
        gritter_success('Temporary cleared');
        return redirect('accounting/point/cut-off/fixed-assets/create');    
    }

    public function cancel()
    {
        $permission_slug = app('request')->input('permission_slug');
        $formulir_id = app('request')->input('formulir_id');

        \DB::beginTransaction();

        FormulirHelper::cancel($permission_slug, $formulir_id);
        CutOffHelper::cancel($formulir_id);

        \DB::commit();

        return array('status' => 'success');
    }
}

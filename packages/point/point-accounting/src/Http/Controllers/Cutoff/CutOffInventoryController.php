<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use Point\Core\Helpers\TempDataHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\Warehouse;
use Point\PointAccounting\Helpers\CutOffHelper;
use Point\PointAccounting\Helpers\CutOffInventoryHelper;
use Point\PointAccounting\Http\Requests\CutOffSubledgerRequest;
use Point\PointAccounting\Models\CutOffInventory;
use Point\PointAccounting\Models\CutOffInventoryDetail;

class CutOffInventoryController extends Controller
{	
	use ValidationTrait;

	public function index()
	{
		access_is_allowed('read.point.accounting.cut.off.inventory');

		$view = view('point-accounting::app.accounting.point.cut-off.inventory.index');
       	$view->list_cut_off = CutOffInventory::joinFormulir()
            ->notArchived()
            ->selectOriginal()
            ->orderByStandard()
            ->groupBy('formulir_id')
            ->paginate(100);
        return $view;
	}

	public function create()
	{
		access_is_allowed('create.point.accounting.cut.off.inventory');
		
		$view = view('point-accounting::app.accounting.point.cut-off.inventory.create');
        $view->list_coa = Coa::active()->where('coa_category_id', 4)->get(); // get all coa where category inventory
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function store(CutOffSubledgerRequest $request)
	{
		formulir_is_allowed_to_create('create.point.accounting.cut.off.inventory', date_format_db($request->input('form_date')), []);
		
		\DB::beginTransaction();

        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffInventory()));
        $formulir = formulir_create($request->input(), 'point-accounting-cut-off-inventory');
        $cut_off_inventory = CutOffInventoryHelper::create($formulir);
        timeline_publish('create.point.accounting.cut.off.inventory','user '.\Auth::user()->name.' successfully create cut off inventory ' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.inventory', auth()->user()->id);
        gritter_success('Form cut off inventory "'. $formulir->form_number .'" Success to add');
        return redirect('accounting/point/cut-off/inventory/'.$cut_off_inventory->id);
    }

	public function show($id)
	{
		access_is_allowed('read.point.accounting.cut.off.inventory');
		$view = view('point-accounting::app.accounting.point.cut-off.inventory.show');
        $view->list_coa = Coa::active()->where('coa_category_id', 4)->get();
        $view->cut_off_inventory = CutOffInventory::find($id);
        $view->list_cut_off_inventory_archived = CutOffInventory::joinFormulir()->archived($view->cut_off_inventory->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_cut_off_inventory_archived->count();
        return $view;
	}

	public function archived($id)
	{
		access_is_allowed('read.point.accounting.cut.off.inventory');
        $view = view('point-accounting::app.accounting.point.cut-off.inventory.archived');
        $view->cut_off_inventory_archived = CutOffInventory::find($id);
        $view->list_coa = Coa::active()->where('coa_category_id', 4)->get();

        return $view;
    }

	public function edit($id)
	{
		access_is_allowed('update.point.accounting.cut.off.inventory');

		$view = view('point-accounting::app.accounting.point.cut-off.inventory.edit');
		$view->cut_off_inventory = CutOffInventory::find($id);
        self::restoreToTemp($view->cut_off_inventory);
        $view->list_coa = Coa::active()->where('coa_category_id', 4)->get();
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
	}

	public function update(CutOffSubledgerRequest $request, $id)
	{  
        $formulir_check = Formulir::find($id);
		
        formulir_is_allowed_to_update('update.point.accounting.cut.off.inventory', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);

        \DB::beginTransaction();

        $request['formulir_id'] = $formulir_check->id;
        CutOffHelper::checkingDailyCutOff($request, get_class(new CutOffInventory()));
        CutOffHelper::cancel($id);
        $formulir_old = FormulirHelper::archive($request->input(), $id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $cut_off = CutOffInventoryHelper::create($formulir);
        FormulirHelper::clearRelation($formulir_old);
        timeline_publish('update.point.accounting.cut.off.inventory','user '.\Auth::user()->name.' successfully update cut off inventory' . $formulir->form_number);

        \DB::commit();

        TempDataHelper::clear('cut.off.inventory', auth()->user()->id);
        gritter_success('Form cut off inventory "'. $formulir->form_number .'" Success to update');
        return redirect('accounting/point/cut-off/inventory/'.$cut_off->id);
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
        $view = view('point-accounting::app.accounting.point.cut-off.inventory._form-details');

        $view->class = $coa->subledger_type;
        $view->list_master = $coa->subledger_type::active()->get();
       	$view->coa_id = $coa->id;
        $view->list_warehouse = Warehouse::all();
       	$view->details = TempDataHelper::getAllRowHaveKeyValue('cut.off.inventory', auth()->user()->id, 'coa_id', $coa->id);
        return $view;

	}

	public function _loadDetailsAccountInventory()
	{
		if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa = Coa::find(\Input::get('coa_id'));
        $view = view('point-accounting::app.accounting.point.cut-off.inventory._details-account-inventory');
        $view->details = CutOffInventoryDetail::where('cut_off_inventory_id',\Input::get('cut_off_id'))->where('coa_id',\Input::get('coa_id'))->get();
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
        TempDataHelper::removeRowHaveKeyValue('cut.off.inventory', auth()->user()->id, 'coa_id', $coa_id);

        for ($i=0; $i < count(\Input::get('item_id')); $i++) { 
        	
            $keys = [
                'coa_id'=> $coa_id,
                'type'=> 'Point\Framework\Models\Master\Item',
                'item_id'=> \Input::get('item_id')[$i],
                'warehouse_id'=> \Input::get('warehouse_id')[$i],
                'stock_in_db'=> number_format_db(\Input::get('stock_in_db')[$i]),
                'stock'=> number_format_db(\Input::get('stock')[$i]),
                'notes'=> \Input::get('notes')[$i],
                'amount'=> number_format_db(\Input::get('amount')[$i]),
                'unit1'=> \Input::get('unit1')[$i],
                'unit2'=> \Input::get('unit2')[$i]
            ];

	        self::storeTempInventory($keys);	
            $total += number_format_db(\Input::get('amount')[$i]);
        }
        
        $response = array('status' => 'success', 'total' => $total);
        return response()->json($response);
	}

    public function restoreToTemp($cut_off_inventory)
	{
        $check_coa = TempDataHelper::get('cut.off.inventory', auth()->user()->id);
        if(count($check_coa) != null){
            return false;
        }
        TempDataHelper::clear('cut.off.inventory', auth()->user()->id);

        $cut_off_inventory_detail = CutOffInventoryDetail::where('cut_off_inventory_id', $cut_off_inventory->id)->get();
        foreach ($cut_off_inventory_detail as $inventory) {
            
            $item_unit = ItemUnit::where('item_id', $inventory->subledger_id)->where('as_default', 1)->first()->name;
            
            $keys = [
                'coa_id'=> $inventory->coa_id,
                'type'=> 'Point\Framework\Models\Master\Item',
                'item_id'=> $inventory->subledger_id,
                'warehouse_id'=> $inventory->warehouse_id,
                'stock_in_db'=> number_format_db($inventory->stock_in_database),
                'stock'=> number_format_db($inventory->stock),
                'notes'=> $inventory->notes,
                'amount'=> number_format_db($inventory->amount),
                'unit1'=> $item_unit,
                'unit2'=> $item_unit
            ];

            if ($inventory->subledger_id) {
                self::storeTempInventory($keys);    
            }
        }

	}

    public function storeTempInventory($keys)
    {
        $temp = new Temp;
        $temp->name = 'cut.off.inventory';
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
        TempDataHelper::removeRowHaveKeyValue('cut.off.inventory', auth()->user()->id, 'coa_id', \Input::get('modal_coa_id'));
        $response = array('status' => 'success');
        return response()->json($response); 
    }

    public function clearTmp()
    {
        TempDataHelper::clear('cut.off.detail', auth()->user()->id);
        TempDataHelper::clear('cut.off', auth()->user()->id);
        gritter_success('Temporary cleared');
        return redirect('accounting/point/cut-off/inventory/create');    
    }
}

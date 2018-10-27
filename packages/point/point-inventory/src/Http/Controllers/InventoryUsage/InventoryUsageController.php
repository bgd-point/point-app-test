<?php

namespace Point\PointInventory\Http\Controllers\InventoryUsage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;
use Point\PointInventory\Helpers\InventoryUsageHelper;
use Point\PointInventory\Vesa\InventoryUsageVesa;
use Point\PointInventory\Http\Requests\InventoryUsageRequest;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;
use Point\Framework\Models\Master\Coa;

class InventoryUsageController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.inventory.usage');

        $list_inventory_usage = InventoryUsage::joinFormulir()
            ->joinWarehouse()
            ->notArchived()
            ->selectOriginal();

        $list_inventory_usage = InventoryUsageHelper::searchList(
            $list_inventory_usage,
            app('request')->input('order_by'),
            app('request')->input('order_type'),
            app('request')->input('status'),
            app('request')->input('date_from'),
            app('request')->input('date_to'),
            app('request')->input('search')
        );

        $view = view('point-inventory::app.inventory.point.inventory-usage.index');
        $view->list_inventory_usage = $list_inventory_usage->paginate(100);
        
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.inventory.usage');

        $view = view('point-inventory::app.inventory.point.inventory-usage.create');
        $view->list_employee = PersonHelper::getByType(['employee']);
        $view->list_item = Item::active()->paginate(2);
        $view->list_warehouse = Warehouse::all();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_coa = COA::position('Expense')->active()->select('coa.id', 'coa.name', 'coa.coa_number')->get();
        $view->default_coa = JournalHelper::getAccount('point inventory usage', 'inventory differences');
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InventoryUsageRequest $request)
    {
        formulir_is_allowed_to_create('create.point.inventory.usage', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = formulir_create($request->input(), 'point-inventory-inventory-usage');
        $inventory_usage = InventoryUsageHelper::create($formulir);
        timeline_publish('create.point.inventory.usage', 'user '.\Auth::user()->name.' successfully create inventory usage ' . $formulir->form_number);

        DB::commit();

        gritter_success('Form inventory usage "'. $formulir->form_number .'" Success to add');
        return redirect('inventory/point/inventory-usage/'.$inventory_usage->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.inventory.usage');

        $view = view('point-inventory::app.inventory.point.inventory-usage.show');
        $view->inventory_usage = InventoryUsage::find($id);
        $view->list_inventory_usage_archived = InventoryUsage::joinFormulir()->archived($view->inventory_usage->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_inventory_usage_archived->count();
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
        access_is_allowed('update.point.inventory.usage');

        $view = view('point-inventory::app.inventory.point.inventory-usage.edit');
        $view->inventory_usage = InventoryUsage::find($id);
        $view->list_employee = PersonHelper::getByType(['employee']);
        $view->list_item = Item::active()->paginate(100);
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_coa = COA::position('Expense')->active()->select('coa.id', 'coa.name', 'coa.coa_number')->get();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.inventory.usage');

        $view = view('point-inventory::app.inventory.point.inventory-usage.archived');
        $view->inventory_usage_archived = InventoryUsage::find($id);
        $view->inventory_usage = InventoryUsage::joinFormulir()->notArchived($view->inventory_usage_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(InventoryUsageRequest $request, $id)
    {
        $formulir_check = Formulir::find($id);

        formulir_is_allowed_to_update('update.point.inventory.usage', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);

        DB::beginTransaction();

        $formulir_old = formulir_archive($request->input(), $formulir_check->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_check->form_number, $formulir_check->form_raw_number);
        $inventory_usage = InventoryUsageHelper::create($formulir);
        timeline_publish('update.point.inventory.usage', 'user '.\Auth::user()->name.' successfully update inventory usage ' . $formulir->form_number);

        DB::commit();

        gritter_success('Form inventory usage "'. $formulir->form_number .'" Success to update', 'false');
        return redirect('inventory/point/inventory-usage/'.$inventory_usage->id);
    }

    public function exportPDF($id)
    {
        $inventory_usage = InventoryUsage::find($id);
        $warehouse = Warehouse::find($inventory_usage->warehouse_id);

        $data = array(
            'inventory_usage' => $inventory_usage,
            'warehouse' => $warehouse
        );

        $pdf = \PDF::loadView('point-inventory::emails.inventory.point.external.inventory-usage', $data);
        return $pdf->stream($inventory_usage->formulir->form_number.'.pdf');
    }
}

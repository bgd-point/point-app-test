<?php

namespace Point\PointInventory\Http\Controllers\StockCorrection;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;
use Point\PointInventory\Vesa\StockCorrectionVesa;
use Point\PointInventory\Helpers\StockCorrectionHelper;
use Point\PointInventory\Http\Requests\CorrectionRequest;
use Point\PointInventory\Models\StockCorrection\StockCorrection;

class StockCorrectionController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.inventory.stock.correction');

        $list_stock_correction = StockCorrection::joinFormulir()
            ->joinWarehouse()
            ->notArchived()
            ->selectOriginal();

        $list_stock_correction = StockCorrectionHelper::searchList(
            $list_stock_correction,
            app('request')->input('order_by'),
            app('request')->input('order_type'),
            app('request')->input('status'),
            app('request')->input('date_from'),
            app('request')->input('date_to'),
            app('request')->input('search')
        );

        $view = view('point-inventory::app.inventory.point.stock-correction.index');
        $view->list_stock_correction = $list_stock_correction->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.inventory.stock.correction');

        $view = view('point-inventory::app.inventory.point.stock-correction.create');
        $view->list_item = Item::active()->paginate(2);
        $view->list_warehouse = Warehouse::all();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CorrectionRequest $request)
    {
        formulir_is_allowed_to_create('create.point.inventory.stock.correction', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = formulir_create($request->input(), 'point-inventory-stock-correction');
        $stock_correction = StockCorrectionHelper::create($formulir);
        timeline_publish('create.point.inventory.stock.correction', 'user '.\Auth::user()->name.' successfully create stock correction ' . $formulir->form_number);

        DB::commit();

        gritter_success('Form Stock Correction "'. $formulir->form_number .'" Success to add');
        return redirect('inventory/point/stock-correction/'.$stock_correction->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.inventory.stock.correction');

        $view = view('point-inventory::app.inventory.point.stock-correction.show');
        $view->stock_correction = StockCorrection::find($id);
        $view->list_stock_correction_archived = StockCorrection::joinFormulir()->archived($view->stock_correction->form_number)->selectOriginal()->get();
        $view->revision = $view->list_stock_correction_archived->count();
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
        access_is_allowed('update.point.inventory.stock.correction');

        $view = view('point-inventory::app.inventory.point.stock-correction.edit');
        $view->stock_correction = StockCorrection::find($id);
        $view->list_item = Item::active()->paginate(2);
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.inventory.stock.correction');

        $view = view('point-inventory::app.inventory.point.stock-correction.archived');
        $view->stock_correction_archived = StockCorrection::find($id);
        $view->stock_correction = StockCorrection::joinFormulir()->notArchived($view->stock_correction_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(CorrectionRequest $request, $id)
    {
        $formulir_check = Formulir::find($id);

        formulir_is_allowed_to_update('update.point.inventory.stock.correction', $formulir_check->form_date, $formulir_check);

        DB::beginTransaction();

        $formulir_old = formulir_archive($request->input(), $formulir_check->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_check->form_number, $formulir_check->form_raw_number);
        $stock_correction = StockCorrectionHelper::create($formulir);
        timeline_publish('update.point.inventory.stock.correction', 'user '.\Auth::user()->name.' successfully update stock correction ' . $formulir->form_number);

        DB::commit();

        gritter_success('Form Stock Correction "'. $formulir->form_number .'" Success to update', false);
        return redirect('inventory/point/stock-correction/'.$stock_correction->id);
    }

    /**
     * get item quantity from ajax request
     * @return \Illuminate\Http\JsonResponse
     */
    public function _quantity()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $date_from = date_format_db(\Input::get('form_date'), \Input::get('time'));
        $item_id = \Input::get('item_id');
        $warehouse_id = \Input::get('warehouse_id');
        $quantity = InventoryHelper::getAvailableStock($date_from, $item_id, $warehouse_id);

        $response = array(
            'value' =>$quantity,
        );
        
        return response()->json($response);
    }

    public function _getItemHasAvailableStock()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        
        $date = date_format_db(\Input::get('form_date'), \Input::get('time'));
        $warehouse_id = \Input::get('warehouse_id');

        $inventory = InventoryHelper::getAllAvailableStock($date, $warehouse_id);
        $list_item = [];
        foreach ($inventory as $inventory_detail) {
            array_push($list_item, ['text'=>$inventory_detail->item->codeName, 'value'=>$inventory_detail->item_id]);
        }
        $response = array(
            'lists' => $list_item
        );

        return response()->json($response);
    }
}

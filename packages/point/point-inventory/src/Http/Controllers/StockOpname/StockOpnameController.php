<?php

namespace Point\PointInventory\Http\Controllers\StockOpname;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;
use Point\PointInventory\Vesa\StockOpnameVesa;
use Point\PointInventory\Helpers\StockOpnameHelper;
use Point\PointInventory\Http\Requests\StockOpnameRequest;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;

class StockOpnameController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.inventory.stock.opname');

        $list_stock_opname = StockOpname::joinFormulir()
            ->joinWarehouse()
            ->notArchived()
            ->selectOriginal();

        $list_stock_opname = StockOpnameHelper::searchList(
            $list_stock_opname,
            app('request')->input('order_by'),
            app('request')->input('order_type'),
            app('request')->input('status'),
            app('request')->input('date_from'),
            app('request')->input('date_to'),
            app('request')->input('search')
        );
        
        $view = view('point-inventory::app.inventory.point.stock-opname.index');
        $view->list_stock_opname = $list_stock_opname->paginate(100);
        
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.inventory.stock.opname');

        $view = view('point-inventory::app.inventory.point.stock-opname.create');
        $view->list_warehouse = Warehouse::all();
        $view->list_user_approval = UserHelper::getAllUser();
        $view->details = TempDataHelper::get('stock.opname', auth()->user()->id);
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
        formulir_is_allowed_to_create('create.point.inventory.stock.opname', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = formulir_create($request->input(), 'point-inventory-stock-opname');
        $stock_opname = StockOpnameHelper::create($formulir);
        timeline_publish('create.point.inventory.stock.opname', 'user '.\Auth::user()->name.' successfully create stock opname ' . $formulir->form_number);

        DB::commit();

        TempDataHelper::clear('stock.opname', auth()->user()->id);
        gritter_success('Form stock opname "'. $formulir->form_number .'" Success to add');
        return redirect('inventory/point/stock-opname/'.$stock_opname->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.inventory.stock.opname');

        $view = view('point-inventory::app.inventory.point.stock-opname.show');
        $view->stock_opname = StockOpname::find($id);
        $view->list_stock_opname_archived = StockOpname::joinFormulir()->archived($view->stock_opname->formulir->form_number)->selectOriginal()->get();
        
        $view->revision = $view->list_stock_opname_archived->count();
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
        access_is_allowed('update.point.inventory.stock.opname');

        $view = view('point-inventory::app.inventory.point.stock-opname.edit');
        $view->stock_opname = StockOpname::find($id);
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();

        $details = StockOpnameItem::where('stock_opname_id', $id)->get();
        self::storeTemp($details);
        $view->details = TempDataHelper::get('stock.opname', auth()->user()->id);
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.inventory.stock.opname');

        $view = view('point-inventory::app.inventory.point.stock-opname.archived');
        $view->stock_opname_archived = StockOpname::find($id);
        $view->stock_opname = StockOpname::joinFormulir()->notArchived($view->stock_opname_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(StockOpnameRequest $request, $id)
    {
        $formulir_check = Formulir::find($id);

        formulir_is_allowed_to_update('update.point.inventory.stock.opname', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);

        DB::beginTransaction();

        formulir_archive($request->input(), $formulir_check->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_check->form_number, $formulir_check->form_raw_number);
        $stock_opname = StockOpnameHelper::create($formulir);
        timeline_publish('update.point.inventory.stock.opname', 'user '.\Auth::user()->name.' successfully update stock opname ' . $formulir->form_number);

        DB::commit();

        TempDataHelper::clear('stock.opname', auth()->user()->id);
        gritter_success('Form stock opname"'. $formulir->form_number .'" Success to update');
        return redirect('inventory/point/stock-opname/'.$stock_opname->id);
    }

    public function storeTemp($details)
    {
        TempDataHelper::clear('stock.opname', auth()->user()->id);
        foreach ($details as $detail) {
            $temp = new Temp;
            $temp->user_id = auth()->user()->id;
            $temp->name = 'stock.opname';
            $temp->keys = serialize([
                'item_id'=>$detail->item_id,
                'stock_in_database'=>$detail->stock_in_database,
                'quantity_opname'=>$detail->quantity_opname,
                'unit1'=>$detail->unit,
                'unit2'=>$detail->unit,
                'notes'=>$detail->opname_notes
                
            ]);
            $temp->save();
        }

        return true;
    }

    public function clearTemp($id)
    {
        TempDataHelper::clear('stock.opname', auth()->user()->id);
        gritter_success('Temporary item cleared');
        return redirect('inventory/point/stock-opname/'.$id);
    }
}

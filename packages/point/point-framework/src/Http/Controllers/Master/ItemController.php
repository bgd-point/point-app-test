<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Master\History;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemCategory;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\Unit;
use Point\Framework\Models\Master\Warehouse;
use Point\Framework\Models\OpeningInventory;
use Point\PointSales\Helpers\PosHelper;

class ItemController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.item');

        $view = view('framework::app.master.item.index');
        $view->list_item = Item::search(\Input::get('status'), \Input::get('search'))->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.item');

        $view = view('framework::app.master.item.create');
        $inventories_account = CoaCategory::where('name', 'Inventories')->first();
        $view->list_account_asset = $inventories_account->coa;
        $view->list_item_category = ItemCategory::active()->get();
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_unit = Unit::all();
        $view->converter = TempDataHelper::get('converter', app('request')->user()->id, ['is_pagination' => false]);
        $view->list_petty_cash_account = Coa::joinCategory()->where('coa_category.name', '=', 'Petty Cash')->selectOriginal()->get();

        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        access_is_allowed('create.item');

        $this->storeTemp($request);

        $this->validate($request, [
            'item_category_id' => 'required',
            'code' => 'required|unique:item,code',
            'name' => 'required|unique:item,name',
            'account_asset_id' => 'required',
            'default_unit' => 'required',
        ]);

        DB::beginTransaction();

        $item = new Item;
        $item->item_category_id = $request->input('item_category_id');
        $item->code = $request->input('code');
        $item->name = $request->input('name');
        $item->notes = $request->input('notes');
        $item->reminder = $request->input('reminder') == 'on' ? true : false;
        $item->reminder_quantity_minimum = number_format_db($request->input('reminder_quantity_minimum'));
        $item->account_asset_id = $request->input('account_asset_id');
        $item->created_by = auth()->user()->id;
        $item->updated_by = auth()->user()->id;

        if (!$item->save()) {
            gritter_error(trans('framework::framework/master.item.create.failed', ['name' => $item->name]));
            return redirect()->back();
        }

        // default item unit
        $item_unit = new ItemUnit;
        $item_unit->item_id = $item->id;
        $item_unit->name = $request->input('default_unit');
        $item_unit->as_default = true;
        $item_unit->converter = 1;
        $item_unit->created_by = auth()->user()->id;
        $item_unit->updated_by = auth()->user()->id;
        if (!$item_unit->save()) {
            gritter_error(trans('framework::framework/master.item.create.failed', ['name' => $item->name]));
            return redirect()->back();
        }

        //additional converter unit
        $convertion = 1;
        $count = count($request->input('convertion_from'));
        $convertion_from = $request->input('convertion_from');
        $convertion_to = $request->input('convertion_to');

        for ($i = $count; $i > 0; $i--) {
            if ($i == $count) {
                $convertion = $request->input('convertion')[$i - 1];
            } else {
                $convertion = $convertion * $request->input('convertion')[$i - 1];
            }

            if ($i < $count) {
                /**
                 * "Convertion From" in row 2 should match with "Convertion To" in row 3
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | A                | B             |
                 * 3 | C                | A             |
                 *
                 */
                if ($convertion_to[$i - 1] != $convertion_from[$i]) {
                    gritter_error('please input converter correctly');
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion To" in last row should match with "Default Unit"
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | D                | C             |
                 * 3 | B                | A             |
                 * 4
                 * 5 DEFAULT UNIT = A
                 *
                 */
                if ($convertion_to[$count - 1] != $request->input('default_unit')) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion From" should not have same value
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | B                | C             |
                 * 3 | B                | A             |
                 *
                 */
                if ($convertion_to[$i - 1] == $convertion_to[$i]) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion Fo" should not have same value
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | A                | C             |
                 * 3 | B                | C             |
                 *
                 */
                if ($convertion_from[$i - 1] == $convertion_from[$i]) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion Foom" should not have same value default unit
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | A                | C             |
                 * 3 | B                | D             |
                 *
                 * DEFAULT A
                 */
                if ($convertion_from[$i - 1] == $request->input('default_unit')) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
            }

            $item_unit = new ItemUnit;
            $item_unit->item_id = $item->id;
            $item_unit->name = $convertion_from[$i - 1];
            $item_unit->as_default = false;
            $item_unit->converter = number_format_db($convertion);
            $item_unit->created_by = auth()->user()->id;
            $item_unit->updated_by = auth()->user()->id;
            if (!$item_unit->save()) {
                gritter_error(trans('framework::framework/master.item.create.failed', ['name' => $item->name]));
                return redirect()->back();
            }
        }

        //setup opening balance
        $inserted = [];

        for ($x = 0; $x < count($request->input('warehouse_id')); $x++) {
            $check_qty = number_format_db($request->input('quantity')[$x]);
            $warehouse = $request->input('warehouse_id')[$x];

            $was_inserted = !in_array($warehouse, $inserted);

            if ($check_qty > 0 && $warehouse && $request->input('form_date') && $was_inserted) {
                array_push($inserted, $warehouse);

                $form_date = date_format_db($request->input('form_date'), 'start');
                $form_number = FormulirHelper::number('opening-inventory', $form_date);
                $formulir = new Formulir;
                $formulir->form_date = $form_date;
                $formulir->form_number = $form_number['form_number'];
                $formulir->form_raw_number = $form_number['raw'];
                $formulir->approval_to = 1;
                $formulir->approval_status = 1;
                $formulir->form_status = 1;
                $formulir->created_by = auth()->user()->id;
                $formulir->updated_by = auth()->user()->id;
                $formulir->save();

                $opening_inventory = new OpeningInventory;
                $opening_inventory->formulir_id = $formulir->id;
                $opening_inventory->item_id = $item->id;
                $opening_inventory->save();

                $formulir->formulirable_type = get_class($opening_inventory);
                $formulir->formulirable_id = $opening_inventory->id;
                $formulir->save();

                // insert new inventory
                $inventory = new Inventory();
                $inventory->formulir_id = $formulir->id;
                $inventory->item_id = $item->id;
                $inventory->quantity = number_format_db($request->input('quantity')[$x]) * $item->unit()->first()->converter;
                $inventory->price = number_format_db($request->input('cogs')[$x]) / $item->unit()->first()->converter;
                $inventory->form_date = $form_date;
                $inventory->warehouse_id = $warehouse;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();

                // JOURNAL #1 of #2
                $position = JournalHelper::position($request->input('account_asset_id'));
                $journal = new Journal();
                $journal->form_date = $form_date;
                $journal->coa_id = $request->input('account_asset_id');
                $journal->description = 'opening balance ' . $item->codeName;
                $journal->$position = $inventory->quantity * $inventory->price;
                $journal->form_journal_id = $opening_inventory->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $item->id;
                $journal->subledger_type = get_class($item);
                $journal->save();

                // JOURNAL #2 of #2
                $retained_earning_account = JournalHelper::getAccount('opening balance inventory', 'retained earning');
                $position = JournalHelper::position($retained_earning_account);
                $journal = new Journal();
                $journal->form_date = $form_date;
                $journal->coa_id = $retained_earning_account;
                $journal->description = 'opening balance ' . $item->codeName;
                $journal->$position = $inventory->quantity * $inventory->price;
                $journal->form_journal_id = $formulir->id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }

        // clear converter in temp
        TempDataHelper::clear('converter', auth()->user()->id);

        timeline_publish('create.item', trans('framework::framework/master.item.create.timeline', ['name' => $item->name]));
        DB::commit();

        gritter_success(trans('framework::framework/master.item.create.success', ['name' => $item->name]));
        return redirect()->back();
    }

    public function storeTemp($request)
    {
        // clear converter in temp
        TempDataHelper::clear('converter', auth()->user()->id);

        $count = count($request->input('convertion_from'));
        $convertion_from = $request->input('convertion_from');
        $convertion_to = $request->input('convertion_to');
        $convertion = $request->input('convertion');

        for ($i = 0; $i < $count; $i++) {
            $temp = new Temp;
            $temp->user_id = auth()->user()->id;
            $temp->name = 'converter';
            $temp->keys = serialize([
                'convert_from' => $convertion_from[$i],
                'convert_to' => $convertion_to[$i],
                'convertion' => $convertion[$i]
            ]);
            $temp->save();
        }

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.item');

        $view = view('framework::app.master.item.show');
        $view->item = Item::find($id);
        $view->item_unit = ItemUnit::where('item_id', $id)->orderBy('converter', 'desc')->get();
        $view->unit_default = ItemUnit::where('item_id', $id)->where('as_default', '1')->get();
        $view->histories = History::show('item', $id);
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.item');

        self::insertTempEdit($id);
        $view = view('framework::app.master.item.edit');
        $inventories_account = CoaCategory::where('name', 'Inventories')->first();
        $view->list_account_asset = $inventories_account->coa;
        $view->list_item_category = ItemCategory::active()->get();
        $view->item = Item::find($id);
        $view->unit_default = ItemUnit::where('item_id', $id)->where('as_default', '1')->get();
        $view->list_unit = Unit::all();
        $view->converter = TempDataHelper::get('converter', auth()->user()->id, ['is_pagination' => true]);
        return $view;
    }

    public function insertTempEdit($id)
    {
        $unit = ItemUnit::where('item_id', $id)->orderBy('converter', 'desc')->get();
        TempDataHelper::clear('converter', auth()->user()->id);
        for ($i = 0; $i < count($unit) - 1; $i++) {
            $converter = $unit[$i]['converter'];
            $converter = $converter / $unit[$i + 1]['converter'];
            $description = "1 " . $unit[$i]['name'] . " = " . $converter . " " . $unit[$i + 1]['name'];

            $temp = new Temp;
            $temp->user_id = auth()->user()->id;
            $temp->name = 'converter';
            $temp->keys = serialize([
                'convert_from' => $unit[$i]['name'],
                'convert_to' => $unit[$i + 1]['name'],
                'convertion' => $converter
            ]);
            $temp->save();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        access_is_allowed('update.item');

        DB::beginTransaction();
        $this->validate($request, [
            'name' => 'required|unique:item,name,' . $id,
            'account_asset_id' => 'required'
        ]);

        $item = Item::find($id);
        $item->name = $request->input('name');
        $item->notes = $request->input('notes');
        $item->reminder = $request->input('reminder') == 'on' ? true : false;
        $item->reminder_quantity_minimum = number_format_db($request->input('reminder_quantity_minimum'));
        $item->created_by = auth()->user()->id;
        $item->updated_by = auth()->user()->id;
        $item->account_asset_id = $request->input('account_asset_id');

        if (!$item->save()) {
            gritter_error(trans('framework::framework/master.item.update.failed', ['name' => $item->name]));
            return redirect()->back();
        }

        // delete unit Item old
        $unit_old = ItemUnit::where('item_id', $id);
        $unit_old->delete();

        // default item unit
        $item_unit = new ItemUnit;
        $item_unit->item_id = $item->id;
        $item_unit->name = $request->input('default_unit');
        $item_unit->as_default = true;
        $item_unit->converter = 1;
        $item_unit->created_by = auth()->user()->id;
        $item_unit->updated_by = auth()->user()->id;
        if (!$item_unit->save()) {
            gritter_error(trans('framework::framework/master.item.create.failed', ['name' => $item->name]));
            return redirect()->back();
        }

        //additional converter unit
        $convertion = 1;
        $count = count($request->input('convertion_from'));
        $convertion_from = $request->input('convertion_from');
        $convertion_to = $request->input('convertion_to');

        for ($i = $count; $i > 0; $i--) {
            if ($i == $count) {
                $convertion = $request->input('convertion')[$i - 1];
            } else {
                $convertion = $convertion * $request->input('convertion')[$i - 1];
            }

            if ($i < $count) {
                /**
                 * "Convertion From" in row 2 should match with "Convertion To" in row 3
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | A                | B             |
                 * 3 | C                | A             |
                 *
                 */
                if ($convertion_to[$i - 1] != $convertion_from[$i]) {
                    gritter_error('please input converter correctly');
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion To" in last row should match with "Default Unit"
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | D                | C             |
                 * 3 | B                | A             |
                 * 4
                 * 5 DEFAULT UNIT = A
                 *
                 */
                if ($convertion_to[$count - 1] != $request->input('default_unit')) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion From" should not have same value
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | B                | C             |
                 * 3 | B                | A             |
                 *
                 */
                if ($convertion_to[$i - 1] == $convertion_to[$i]) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion From" should not have same value
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | A                | C             |
                 * 3 | B                | C             |
                 *
                 */
                if ($convertion_from[$i - 1] == $convertion_from[$i]) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
                /**
                 * "Convertion Foom" should not have same value default unit
                 *
                 * 1 | CONVERTION FROM  | CONVERTION TO |
                 * 2 | A                | C             |
                 * 3 | B                | D             |
                 *
                 * DEFAULT A
                 */
                if ($convertion_from[$i - 1] == $request->input('default_unit')) {
                    gritter_error("please input converter correctly");
                    return redirect()->back()->withInput();
                }
            }

            $item_unit = new ItemUnit;
            $item_unit->item_id = $item->id;
            $item_unit->name = $convertion_from[$i - 1];
            $item_unit->as_default = false;
            $item_unit->converter = number_format_db($convertion);
            $item_unit->created_by = auth()->user()->id;
            $item_unit->updated_by = auth()->user()->id;
            if (!$item_unit->save()) {
                gritter_error(trans('framework::framework/master.item.create.failed', ['name' => $item->name]));
                return redirect()->back();
            }
        }

        // clear converter in temp
        TempDataHelper::clear('converter', auth()->user()->id);

        timeline_publish('update.item', trans('framework::framework/master.item.update.timeline', ['name' => $item->name]));
        DB::commit();

        gritter_success(trans('framework::framework/master.item.update.success', ['name' => $item->name]));
        return redirect('master/item/' . $item->id);
    }

    /**
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function delete()
    {
        $redirect = false;

        if (\Input::get('redirect')) {
            $redirect = \Input::get('redirect');
        }

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(auth()->user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        try {
            DB::beginTransaction();
            $item = Item::find(\Input::get('id'));
            $item->delete();

            timeline_publish('delete.item', trans('framework::framework/master.item.delete.timeline', ['name' => $item->name]));

            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Delete Success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success('Delete Item "' . $item->name . '" Success');
        }

        return $response;
    }

    /**
     * @param $item_type_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function category()
    {
        if (!auth()->user()->may('create.item')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.item.category');
        $view->list_item_category = ItemCategory::paginate(100);
        return $view;
    }

    /**
     * get code for item from ajax request
     * @return \Illuminate\Http\JsonResponse
     */
    public function _code()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $item_category = ItemCategory::find(\Input::get('item_category_id'));
        $item = Item::where('item_category_id', '=', $item_category->id)->orderBy('id', 'desc')->first();
        $number = 1;
        if ($item) {
            $array_number = explode('-', $item->code);
            $number = $array_number[count($array_number) - 1] + 1;
        }

        $response = array(
            'code' => $item_category->code . '-' . $number
        );
        return response()->json($response);
    }

    /**
     * get item unit from ajax request
     * @return \Illuminate\Http\JsonResponse
     */
    public function _unit()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $item = Item::find(\Input::get('item_id'));
        $item_unit = [];
        $default_id = 0;
        $unit_default;
        foreach ($item->unit as $unit) {
            if ($unit->converter == 1) {
                $default_id = $unit->id;
                $default_name = $unit->name;
                $unit_default = $unit;
                array_push($item_unit, ['text' => $unit->name, 'value' => $unit->id]);
            } else {
                array_push($item_unit, ['text' => $unit->name . ' ( ' . number_format_quantity($unit->converter) . ' ' . $unit_default->name . ' )', 'value' => $unit->id]);
            }
        }

        $response = array(
            'lists' => $item_unit,
            'defaultID' => $default_id,
            'default_name' => $default_name
        );
        return response()->json($response);
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.item')) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $item = Item::find($request->input('index'));

        if (!$item) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $item->disabled = $item->disabled == 0 ? 1 : 0;
        $item->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $item->disabled);

        return response()->json($response);
    }

    public function _getStock()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $date = date_format_db(\Input::get('form_date'), \Input::get('time'));
        $warehouse_id = \Input::get('warehouse_id');

        $items = InventoryHelper::getAllAvailableStock($date, $warehouse_id);
        $list_item = [];
        foreach ($items as $item) {
            array_push($list_item, ['text' => $item->codeName, 'value' => $item->item_id]);
        }

        return response()->json([
            'lists' => $list_item
        ]);
    }

    public function _getQuantity()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $date_from = date_format_db(\Input::get('form_date'), \Input::get('time'));
        $item_id = \Input::get('item_id');
        $warehouse_id = \Input::get('warehouse_id');
        $quantity = InventoryHelper::getAvailableStock($date_from, $item_id, $warehouse_id);

        $response = array(
            'value' => $quantity,
        );

        return response()->json($response);
    }

    public function _list()
    {
        return response()->json(array(
            'lists' => Item::active()
                ->select('id as value', DB::raw('CONCAT("[", code, "] ", name) AS text'), 'code')
                ->get()
                ->toArray()
        ));
    }

    public function _listItemHavingQuantity()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        return response()->json(array(
            'lists' => InventoryHelper::getItem(\Carbon::now(), PosHelper::getWarehouse())
        ));
    }

    public function _getPrice()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $item_id = \Input::get('item_id');
        $inventory = Inventory::where('item_id', $item_id)->orderBy('id', 'DESC')->where('quantity', '>', 0)->first();

        $price = 0;
        if ($inventory) {
            $price = $inventory->price;
        }
        
        $response = array(
            'price' => number_format_quantity($price, 0),
        );

        return response()->json($response);
    }

    public function _create(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        access_is_allowed('create.item');

        $validator = \Validator::make($request->all(), [
            'item_category_id' => 'required',
            'name' => 'required|unique:item,name',
            'code' => 'required|unique:item,code',
            'account_asset_id' => 'required',
            'default_unit' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(array('status' => 'failed'));
        }

        DB::beginTransaction();

        $item = new Item;
        $item->item_type_id = 1;
        $item->item_category_id = $request->input('item_category_id');
        $item->code = $request->input('code');
        $item->name = $request->input('name');
        $item->notes = $request->input('notes');
        $item->reminder = false;
        $item->reminder_quantity_minimum = 0;
        $item->account_asset_id = $request->input('account_asset_id');
        $item->created_by = auth()->user()->id;
        $item->updated_by = auth()->user()->id;
        $item->save();

        // Item Unit
        $item_unit = new ItemUnit;
        $item_unit->item_id = $item->id;
        $item_unit->name = $request->input('default_unit');
        $item_unit->as_default = true;
        $item_unit->converter = 1;
        $item_unit->created_by = auth()->user()->id;
        $item_unit->updated_by = auth()->user()->id;
        $item_unit->save();

        DB::commit();

        return response()->json(
            array(
                'status' => 'success',
                'lists' => ['text' => $item->codeName, 'value' => $item->id, 'code'=> $item->code],
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'notes' => $item->notes,
                'codeName' => $item->codeName,
                'codeNameNotes' => $item->codeName ." ". $item->notes
            )
        );
    }

    public function export(Request $request)
    {
        $list_item = Item::search(\Input::get('status'), \Input::get('search'))->get()->toArray();
        $storage = public_path('item-report/');
        $fileName = 'ITEM REPORT '.date('YmdHis');
        $request = $request->input();
        \Queue::push(function ($job) use ($list_item, $fileName, $request, $storage) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Excel::create($fileName, function ($excel) use ($list_item, $storage) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($list_item) {
                    $sheet->setWidth(array(
                        'A' => 10,
                        'B' => 25,
                        'C' => 25,
                        'D' => 25,
                        'E' => 25,
                        'F' => 25
                    ));

                    // MERGER COLUMN
                    $sheet->mergeCells('A1:F1', 'center');
                    $sheet->cell('A1', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));

                        $cell->setValue(strtoupper('DATA ITEM'));
                    });

                    $sheet->cell('A2:F2', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    // Generad table of content
                    $header = array(
                        array('NO', 'CATEGORY', 'ACCOUNT NAME', 'ITEM', 'DEFAULT UNIT', 'NOTES')
                    );

                    $total_data = count($list_item);
                    for ($i=0; $i < $total_data; $i++) {
                        array_push($header, [$i + 1,
                            strtoupper(ItemCategory::find($list_item[$i]['item_category_id'])->name),
                            strtoupper(Coa::find($list_item[$i]['account_asset_id'])->name),
                            strtoupper('['.$list_item[$i]['code'].'] ' . $list_item[$i]['name']),
                            strtoupper(Item::defaultUnit($list_item[$i]['id'])->name),
                            strtoupper($list_item[$i]['notes'])
                        ]);
                    }

                    $total_data = $total_data+2;
                    $sheet->fromArray($header, null, 'A2', false, false);
                    $sheet->setBorder('A2:F'.$total_data, 'thin');

                    $next_row = $total_data + 1;
                    $sheet->cell('A'.$next_row, function ($cell) {
                        $cell->setValue('TOTAL');
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('B'.$next_row, function ($cell) use ($list_item) {
                        $cell->setValue(count($list_item));
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12'
                        ));
                    });
                    $next_row = $next_row + 2;
                    $sheet->cell('A'.$next_row, function ($cell) {
                        $cell->setValue('DOWNLOAD AT '. date('Y-m-d H:i:s'));
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12'
                        ));
                    });
                });
            })->store('xls', $storage);
            $job->delete();
        });
        
        $data = [
            'username' => auth()->user()->name,
            'link' => url('item-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('framework::email.item-report', $data, function ($message) use ($data) {
                $message->to($data['email'])->subject('ITEM REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}

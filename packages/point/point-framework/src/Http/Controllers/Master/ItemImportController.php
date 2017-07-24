<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\ImportHelper;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Http\Controllers\Controller;
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
use Point\Framework\Models\SettingJournal;

class ItemImportController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        $view = view()->make('framework::app.master.item.import');
        $view->list_import = TempDataHelper::getPagination('item.import', auth()->user()->id);
        $view->success = TempDataHelper::get('item.import', auth()->user()->id, ['is_pagination' => true]);
        $view->error = TempDataHelper::get('item.import.error', auth()->user()->id, ['is_pagination' => true]);
        return $view;
    }

    public function download()
    {
        \Excel::create('Item', function ($excel) {
            # Sheet Item Import
            $excel->sheet('Item Data', function ($sheet) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                    'C' => 25,
                    'D' => 25,
                    'E' => 25,
                    'F' => 25,
                    'G' => 25,
                    'H' => 25,
                    'J' => 25,
                ));
                $array = array(
                    array('NO', 'ASSET ACCOUNT', 'CATEGORY', 'WAREHOUSE', 'NAME', 'UNIT', 'QUANTITY', 'COST OF SALE', 'NOTES')
                );

                $sheet->fromArray($array, null, 'A1', false, false);
            });

            # Sheet Master Category
            $excel->sheet('Master Category', function ($sheet) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                ));

                $list_item_category = ItemCategory::active()->get();
                $array_category = array(array('NO', 'NAME'));
                for ($i = 0; $i < count($list_item_category); $i++) {
                    array_push($array_category, [$i + 1, $list_item_category[$i]['name']]);
                }
                $sheet->fromArray($array_category, null, 'A1', false, false);
            });

            # Sheet Master Warehouse
            $excel->sheet('Master Warehouse', function ($sheet) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                ));

                $list_warehouse = Warehouse::active()->get();
                $array_warehouse = array(array('NO', 'NAME'));
                for ($i = 0; $i < count($list_warehouse); $i++) {
                    array_push($array_warehouse, [$i + 1, $list_warehouse[$i]['name']]);
                }
                $sheet->fromArray($array_warehouse, null, 'A1', false, false);
            });

            # Sheet Master Unit
            $excel->sheet('Master Unit', function ($sheet) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                ));

                $list_unit = Unit::get();
                $array_unit = array(array('NO', 'NAME'));
                for ($i = 0; $i < count($list_unit); $i++) {
                    array_push($array_unit, [$i + 1, $list_unit[$i]['name']]);
                }
                $sheet->fromArray($array_unit, null, 'A1', false, false);
            });

            # Sheet Master COA
            $excel->sheet('Master Asset Account', function ($sheet) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                ));
                $coa_category = CoaCategory::where('name', '=', 'Inventories')->first();
                $array_coa = array(array('NO', 'NAME'));
                $i = 0;
                if ($coa_category) {
                    foreach ($coa_category->coa as $coa) {
                        array_push($array_coa, [++$i, $coa->name]);
                    }
                }
                $sheet->fromArray($array_coa, null, 'A1', false, false);
            });
        })->export('xls');
        return redirect()->back();
    }

    public function upload(Request $request)
    {
        ImportHelper::xlsValidate();
        try {
            $filePath = $request->project->url . '/item/';
            $fileName = auth()->user()->id . '' . date('Y-m-d_His') . '.xls';
            $fileLink = $filePath . $fileName;
            if (app('request')->hasFile('file')) {
                \Storage::put($fileLink, file_get_contents($request->file('file')));
            }
            $request = $request->input();
            self::checkingIndexArray($request, $fileLink);
            \Queue::push(function ($job) use ($fileLink, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Excel::selectSheets('Item Data')->load('storage/app/' . $fileLink, function ($reader) use ($request) {
                    $results = $reader->get()->toArray();

                    TempDataHelper::clear('item.import.error', $request['user']->id);
                    TempDataHelper::clear('item.import', $request['user']->id);

                    foreach ($results as $data) {
                        $warehouse = Warehouse::where('name', $data['warehouse'])->first();
                        $unit = Unit::where('name', $data['unit'])->first();
                        $item_category = ItemCategory::where('name', $data['category'])->first();
                        $coa = Coa::where('name', $data['asset_account'])->first();

                        # if this item found in database, so we skip this data
                        $item = Item::where('name', '=', $data['name'])->orderBy('id', 'desc')->first();
                        $item_exist = false;
                        if ($item) {
                            $item_exist = true;
                        }

                        # Check if warehouse, unit and category match in database
                        $temp_name = 'item.import.error';
                        if ($warehouse && $unit && $item_category && $coa && $item_exist === false) {
                            $temp_name = 'item.import';
                        }

                        # Check if quantity not set
                        $quantity = 0;
                        if (!empty($data['quantity']) && is_numeric($data['quantity'])) {
                            $quantity = number_format_db($data['quantity']);
                        }

                        # Check if price not set
                        $cost_of_sale = 0;
                        if (!empty($data['cost_of_sale']) && is_numeric($data['cost_of_sale'])) {
                            $cost_of_sale = number_format_db($data['cost_of_sale']);
                        }

                        $temp = new Temp;
                        $temp->name = $temp_name;
                        $temp->user_id = $request['user']->id;
                        $temp->keys = serialize([
                            'asset_account' => $data['asset_account'],
                            'category' => $data['category'],
                            'warehouse' => $data['warehouse'],
                            'name' => $data['name'],
                            'unit' => $data['unit'],
                            'quantity' => $quantity,
                            'cost_of_sale' => $cost_of_sale,
                            'notes' => $data['notes'],
                        ]);
                        $temp->save();
                    }

                    /*
                     * TODO: this vesa bugged cannot removed from list
                    $vesa = new Vesa;
                    $vesa->task_date = \Carbon::now();
                    $vesa->task_deadline = \Carbon::now();
                    $vesa->taskable_type = null;
                    $vesa->taskable_id = null;
                    $vesa->task_action = 'create';
                    $vesa->description = 'Import item success, please check and submit';
                    $vesa->permission_slug = 'create.item';
                    $vesa->url = 'master/item/import';
                    $vesa->save();
                    */
                });

                $job->delete();
            });
        } catch (\Exception $e) {
            gritter_error($e->getMessage());
            return redirect()->back();
        }

        gritter_success('upload data success, please wait a second and refresh your page');
        return redirect('master/item/import');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required'
        ]);

        $request = $request->input();
        $user_id = auth()->user()->id;
        \Queue::push(function ($job) use ($user_id, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            $import = TempDataHelper::get('item.import', $user_id);
            for ($i = 0; $i < count($import); $i++) {
                # if this item found in database, so we skip this data
                $item = Item::where('name', '=', $import[$i]['name'])->orderBy('id', 'desc')->first();
                if ($item) {
                    continue;
                }

                DB::beginTransaction();

                # initialize data
                $asset_account = Coa::where('name', $import[$i]['asset_account'])->first();
                $warehouse = Warehouse::where('name', $import[$i]['warehouse'])->first();
                $unit = Unit::where('name', $import[$i]['unit'])->first();
                $item_category = ItemCategory::where('name', '=', $import[$i]['category'])->first();

                $item = Item::where('item_category_id', '=', $item_category->id)->orderBy('id', 'desc')->first();
                $increment = 1;
                if ($item) {
                    $increment_array = explode('-', $item->code);
                    $increment = end($increment_array) + 1;
                }
                $code = $item_category->code . '-' . $increment;

                $item = new Item;
                $item->item_type_id = 1;
                $item->item_category_id = $item_category->id;
                $item->code = $code;
                $item->name = $import[$i]['name'];
                $item->notes = $import[$i]['notes'];
                $item->account_asset_id = $asset_account->id;
                $item->created_by = $request['user']->id;
                $item->updated_by = $request['user']->id;
                $item->save();

                # default item unit
                $item_unit = new ItemUnit;
                $item_unit->item_id = $item->id;
                $item_unit->name = $unit->name;
                $item_unit->as_default = true;
                $item_unit->converter = 1;
                $item_unit->created_by = $request['user']->id;
                $item_unit->updated_by = $request['user']->id;
                $item_unit->save();

                $quantity = $import[$i]['quantity'];

                if ($quantity > 0) {
                    $cost_of_sale = $import[$i]['cost_of_sale'];

                    $formulir = FormulirHelper::create($request, 'opening-inventory');

                    $opening_inventory = new OpeningInventory;
                    $opening_inventory->formulir_id = $formulir->id;
                    $opening_inventory->item_id = $item->id;
                    $opening_inventory->quantity = $quantity;
                    $opening_inventory->price = $cost_of_sale;
                    $opening_inventory->unit = $unit->name;
                    $opening_inventory->converter = 1;
                    $opening_inventory->save();

                    $formulir->formulirable_type = get_class($opening_inventory);
                    $formulir->formulirable_id = $opening_inventory->id;
                    $formulir->save();

                    $inventory = new Inventory();
                    $inventory->formulir_id = $formulir->id;
                    $inventory->item_id = $item->id;
                    $inventory->quantity = number_format_db($quantity);
                    $inventory->price = number_format_db($cost_of_sale);
                    $inventory->form_date = $formulir->form_date;
                    $inventory->warehouse_id = $warehouse->id;

                    $inventory_helper = new InventoryHelper($inventory);
                    $inventory_helper->in();

                    # JOURNAL #1 of #2
                    $position = JournalHelper::position($asset_account->id);
                    $journal = new Journal();
                    $journal->form_date = $formulir->form_date;
                    $journal->coa_id = $asset_account->id;
                    $journal->description = 'opening balance ' . $item->codeName;
                    $journal->$position = $inventory->quantity * $inventory->price;
                    $journal->form_journal_id = $opening_inventory->formulir_id;
                    $journal->form_reference_id;
                    $journal->subledger_id = $item->id;
                    $journal->subledger_type = get_class($item);
                    $journal->save();

                    # JOURNAL #2 of #2
                    $retained_earning = SettingJournal::where('group', '=', 'opening balance inventory')
                        ->where('name', 'retained earning')->first();

                    if (!$retained_earning) {
                        throw new PointException('Contact administrator to setup account journal');
                    }

                    $position = JournalHelper::position($retained_earning->coa_id);

                    $journal = new Journal();
                    $journal->form_date = $formulir->form_date;
                    $journal->coa_id = $retained_earning->coa_id;
                    $journal->description = 'opening balance ' . $item->codeName;
                    $journal->$position = $inventory->quantity * $inventory->price;
                    $journal->form_journal_id = $formulir->id;
                    $journal->form_reference_id;
                    $journal->subledger_id;
                    $journal->subledger_type;
                    $journal->save();
                }

                TempDataHelper::remove($import[$i]['rowid']);
                DB::commit();
            }
            $job->delete();
        });

        TempDataHelper::clear('item.import.error', auth()->user()->id);
        gritter_success('import item data success, please wait a second to take a change');
        return redirect()->back();
    }

    public function checkingIndexArray($request, $fileLink)
    {
        \Excel::selectSheets('Item Data')->load('storage/app/' . $fileLink, function ($reader) use ($request) {
            $results = $reader->get()->toArray();
            foreach ($results as $data) {
                if (! array_key_exists('asset_account', $data)) {
                    throw new PointException("COLUMN ACCOUNT ASSET NOT FOUND");
                }

                if (! array_key_exists('category', $data)) {
                    throw new PointException("COLUMN CATEGORY NOT FOUND");
                }

                if (! array_key_exists('warehouse', $data)) {
                    throw new PointException("COLUMN WAREHOUSE NOT FOUND");
                }

                if (! array_key_exists('name', $data)) {
                    throw new PointException("COLUMN NAME NOT FOUND");
                }

                if (! array_key_exists('unit', $data)) {
                    throw new PointException("COLUMN UNIT NOT FOUND");
                }

                if (! array_key_exists('quantity', $data)) {
                    throw new PointException("COLUMN QUANTITY NOT FOUND");
                }

                if (! array_key_exists('cost_of_sale', $data)) {
                    throw new PointException("COLUMN COST OF SALE NOT FOUND");
                }

                if (! array_key_exists('notes', $data)) {
                    throw new PointException("COLUMN NOTES NOT FOUND");
                }
            }
        });
    }

    public function deleteRow($id)
    {
        TempDataHelper::remove($id);
        gritter_success('delete success');
        return redirect()->back();
    }

    public function clearTemp()
    {
        TempDataHelper::clear('item.import', auth()->user()->id);
        TempDataHelper::clear('item.import.error', auth()->user()->id);
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function clearErrorTemp()
    {
        TempDataHelper::clear('item.import.error', auth()->user()->id);
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function _updateTemp(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'quantity' => 'required',
            'cos' => 'required',
        ]);

        if ($validator->fails()) {
            $response = array('status' => 'failed');
            return response()->json($response);
        }

        $temp = Temp::find($_POST['row_id']);
        $temp->name = 'item.import';
        $temp->keys = serialize([
            'asset_account' => $_POST['coa'],
            'category' => $_POST['category'],
            'warehouse' => $_POST['warehouse'],
            'name' => $_POST['name'],
            'unit' => $_POST['unit'],
            'quantity' => number_format_db($_POST['quantity']),
            'cost_of_sale' => number_format_db($_POST['cos']),
            'notes' => $_POST['notes'],

        ]);
        $temp->save();
        $response = array(
            'status' => 'success',
            'name' => $_POST['name'],
            'quantity' => $_POST['quantity'],
            'cost_of_sale' => $_POST['cos'],
            'notes' => $_POST['notes'],
        );

        return response()->json($response);
    }
}

<?php

namespace Point\Framework\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Point\Core\Helpers\QueueHelper;
use Point\Framework\Helpers\WarehouseHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;

class InventoryReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        WarehouseHelper::isAvailable();

        $item_search = \Input::get('search');
        $view = view('framework::app.inventory.report.index');
        $view->search_warehouse = \Input::get('warehouse_id') ? Warehouse::find(\Input::get('warehouse_id')) : 0;
        $view->list_warehouse = Warehouse::active()->get();
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        $view->inventory = Inventory::joinItem()
            ->where('item.name', 'like', '%' . $item_search . '%')
            ->groupBy('inventory.item_id')
            ->where('inventory.total_quantity', '>', 0)
            ->where(function ($query) use ($view){
                if ($view->search_warehouse) {
                    $query->where('inventory.warehouse_id', $view->search_warehouse->id);
                }
            })
            ->where(function ($query) use ($view){
                $query->whereBetween('inventory.form_date', [$view->date_from, $view->date_to])
                    ->orWhere('inventory.form_date','<' , $view->date_from);
            })->paginate(100);

        return $view;
    }

    /**
     * Mutasi Inventory.
     *
     * @return \Illuminate\Http\Response
     */
    public function detail($item_id)
    {
        $date_from = date('Y-m-01 00:00:00');
        $date_to = date('Y-m-d 23:59:59');
        $warehouse_id = \Input::get('warehouse_id') ? \Input::get('warehouse_id') : 0;
        $view = view('framework::app.inventory.report.detail');
        $view->date_from = \Input::get('date_from') ? \Input::get('date_from') : $date_from;
        $view->date_to = \Input::get('date_to') ? \Input::get('date_to') : $date_to;
        $view->warehouse = $warehouse_id ? Warehouse::find($warehouse_id) : 0;
        $view->item = Item::find($item_id);
        $view->list_inventory = Inventory::where('item_id', '=', $item_id)
            ->where('item_id', '=', $item_id)
            ->where(function($query) use ($warehouse_id){
                if ($warehouse_id) {
                    $query->where('warehouse_id', '=', $warehouse_id);
                }
            })
            ->where('form_date', '>=', $view->date_from)
            ->where('form_date', '<=', $view->date_to)
            ->orderBy('form_date')
            ->paginate(100);

        return $view;
    }

    public function export(Request $request)
    {
        $item_search = \Input::get('search');
        $view = view('framework::app.inventory.report.index');
        $search_warehouse = \Input::get('warehouse_id') ? Warehouse::find(\Input::get('warehouse_id')) : 0;
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        $inventory = Inventory::joinItem()
            ->where('item.name', 'like', '%' . $item_search . '%')
            ->groupBy('inventory.item_id')
            ->where('inventory.total_quantity', '>', 0)
            ->where(function ($query) use ($view){
                if ($search_warehouse) {
                    $query->where('inventory.warehouse_id', $search_warehouse->id);
                }
            })
            ->where(function ($query) use ($view){
                $query->whereBetween('inventory.form_date', [$date_from, $date_to])
                    ->orWhere('inventory.form_date','<' , $date_from);
            })->get()->toArray();

        $data = array(
            'warehouse_id' => \Input::get('warehouse_id') ? ? Warehouse::find(\Input::get('warehouse_id'))->id : 0,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'item_search' => $item_search,
            'list_inventory' => $inventory,
            'request' => $request->input()

        );
        self::export($data);
    }

    public function export($data)
    {
        $storage = public_path('inventory-report/');
        $fileName = 'Inventory report '.date('YmdHis');
        \Queue::push(function ($job) use ($data, $fileName) {
            QueueHelper::reconnectAppDatabase($data['request']['database_name']);
            \Excel::create($fileName, function ($excel) use ($data, $storage) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($data) {
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

                        $cell->setValue(strtoupper('INVENTORY REPORT'));
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
                        array('NO', 'OPENING STOCK', 'STOCK IN', 'STOCK OUT', 'CLOSING STOCK')
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

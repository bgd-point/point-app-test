<?php

namespace Point\Framework\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\Framework\Helpers\WarehouseHelper;
use Point\Framework\Models\Formulir;
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
            ->where(function ($query) use ($view) {
                if ($view->search_warehouse) {
                    $query->where('inventory.warehouse_id', $view->search_warehouse->id);
                }
            })
            ->where(function ($query) use ($view) {
                $query->whereBetween('inventory.form_date', [$view->date_from, $view->date_to])
                    ->orWhere('inventory.form_date', '<', $view->date_from);
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
            ->where(function ($query) use ($warehouse_id) {
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
        $search_warehouse = \Input::get('warehouse_id') ? Warehouse::find(\Input::get('warehouse_id')) : 0;
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        $inventory = Inventory::joinItem()
            ->where('item.name', 'like', '%' . $item_search . '%')
            ->groupBy('inventory.item_id')
            ->where('inventory.total_quantity', '>', 0)
            ->where(function ($query) use ($search_warehouse) {
                if ($search_warehouse) {
                    $query->where('inventory.warehouse_id', $search_warehouse->id);
                }
            })
            ->where(function ($query) use ($date_from, $date_to) {
                $query->whereBetween('inventory.form_date', [$date_from, $date_to])
                    ->orWhere('inventory.form_date', '<', $date_from);
            })->get()->toArray();

        $data = array(
            'warehouse' => \Input::get('warehouse_id') ? Warehouse::find(\Input::get('warehouse_id'))->id : 0,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'item_search' => $item_search,
            'list_inventory' => $inventory,
            'request' => $request->input(),
        );
        self::generateExcel($request, $data);
    }

    public function exportDetail(Request $request)
    {
        $warehouse_id = \Input::get('warehouse_id') ? \Input::get('warehouse_id') : 0;
        $date_from = \Input::get('date_from');
        $date_to = \Input::get('date_to');
        $item_id = \Input::get('item_id');
        $list_inventory = Inventory::where('item_id', '=', $item_id)
            ->where('item_id', '=', $item_id)
            ->where(function ($query) use ($warehouse_id) {
                if ($warehouse_id) {
                    $query->where('warehouse_id', '=', $warehouse_id);
                }
            })
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->orderBy('form_date')
            ->get()->toArray();

        $data = array(
            'warehouse' => $warehouse_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'item_id' => $item_id,
            'list_inventory' => $list_inventory,
            'request' => $request->input(),
        );
        self::generateExcelDetail($request, $data);
    }

    public function generateExcel($request, $data)
    {
        $storage = storage_path('app/'.$request->project->url.'/inventory-report/');
        $fileName = 'inventory report '.date('YmdHis');
        \Queue::push(function ($job) use ($data, $fileName, $storage) {
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

                    $warehouse = $data['warehouse'] ? Warehouse::find($data['warehouse'])->name : 'ALL';
                    // MERGER COLUMN
                    $sheet->mergeCells('A1:F1', 'center');
                    $sheet->cell('A1', function ($cell) use ($warehouse) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));


                        $cell->setValue(strtoupper('INVENTORY REPORT "'. $warehouse.'"'));
                    });

                    $sheet->cell('A2:F3', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    // Generad header
                    $sheet->mergeCells('A2:A3', 'center');
                    $sheet->mergeCells('B2:B3', 'center');
                    $sheet->cell('A2', function ($cell) {
                        $cell->setValue('NO');
                    });
                    $sheet->cell('B2', function ($cell) {
                        $cell->setValue('ITEM');
                    });
                    $sheet->cell('C2', function ($cell) {
                        $cell->setValue('OPENING STOCK');
                    });

                    $sheet->cell('C3', function ($cell) use ($data) {
                        $cell->setValue(date_format_view($data['date_from']));
                    });

                    $sheet->cell('D2', function ($cell) {
                        $cell->setValue('STOCK IN');
                    });
                    $sheet->cell('D3', function ($cell) use ($data) {
                        $cell->setValue('(' .date_format_view($data['date_from']). ') - (' . date_format_view($data['date_to']) .')');
                    });

                    $sheet->cell('E2', function ($cell) {
                        $cell->setValue('STOCK OUT');
                    });
                    $sheet->cell('E3', function ($cell) use ($data) {
                        $cell->setValue('(' .date_format_view($data['date_from']). ') - (' . date_format_view($data['date_to']) .')');
                    });

                    $sheet->cell('F2', function ($cell) {
                        $cell->setValue('CLOSING STOCK');
                    });
                    $sheet->cell('F3', function ($cell) use ($data) {
                        $cell->setValue(date_format_view($data['date_to']));
                    });

                    $content = [];
                    $total_data = count($data['list_inventory']);
                    for ($i=0; $i < $total_data; $i++) {
                        $item = Item::find($data['list_inventory'][$i]['item_id']);
                        if ($data['warehouse']) {
                            $opening_stock = inventory_get_opening_stock($data['date_from'], $item->id, $data['warehouse']);
                            $stock_in = inventory_get_stock_in($data['date_from'], $data['date_to'], $item->id, $data['warehouse']);
                            $stock_out = inventory_get_stock_out($data['date_from'], $data['date_to'], $item->id, $data['warehouse']);
                            $closing_stock = inventory_get_closing_stock($data['date_from'], $data['date_to'], $item->id, $data['warehouse']);
                            $warehouse = $data['warehouse'];
                        } else {
                            $opening_stock = inventory_get_opening_stock_all($data['date_from'], $item->id);
                            $stock_in = inventory_get_stock_in_all($data['date_from'], $data['date_to'], $item->id);
                            $stock_out = inventory_get_stock_out_all($data['date_from'], $data['date_to'], $item->id);
                            $closing_stock = inventory_get_closing_stock_all($data['date_from'], $data['date_to'], $item->id);
                            $warehouse = 0;
                        }

                        $recalculate_stock = Inventory::where('item_id', '=', $item->id)->where('recalculate', '=', 1)->orderBy('form_date', 'asc')->count() > 0;
                        array_push($content, [$i + 1,
                            strtoupper($item->codeName),
                            strtoupper(number_format_quantity($opening_stock)),
                            strtoupper(number_format_quantity($stock_in)),
                            strtoupper(number_format_quantity($stock_out)),
                            strtoupper(number_format_quantity($closing_stock))
                        ]);
                    }

                    $total_data = $total_data+3;
                    $sheet->fromArray($content, null, 'A4', false, false);
                    $sheet->setBorder('A2:F'.$total_data, 'thin');
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$request->project->url.'/inventory-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $data) {
            QueueHelper::reconnectAppDatabase($data['request']['database_name']);
            \Mail::send('framework::email.inventory-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('INVENTORY REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }

    public function generateExcelDetail($request, $data)
    {
        $item = Item::find($data['item_id']);
        $storage = storage_path('app/'.$request->project->url.'/inventory-report-detail/');
        $fileName = 'inventory report detail '.$item->name. ' '.date('YmdHis');
        \Queue::push(function ($job) use ($data, $fileName, $storage) {
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

                    $warehouse = $data['warehouse'] ? Warehouse::find($data['warehouse'])->name : 'ALL';
                    $item = Item::find($data['item_id']);
                    // MERGER COLUMN
                    $sheet->mergeCells('A1:F1', 'center');
                    $sheet->cell('A1', function ($cell) use ($warehouse, $item) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));


                        $cell->setValue(strtoupper('INVENTORY REPORT ' .$item->codeName));
                    });

                    $sheet->cell('A2:F3', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    // Generad header
                    $sheet->mergeCells('A2:A3', 'center');
                    $sheet->mergeCells('B2:B3', 'center');
                    $sheet->mergeCells('C2:C3', 'center');
                    $sheet->mergeCells('D2:D3', 'center');
                    $sheet->mergeCells('F2:F3', 'center');
                    $sheet->cell('A2', function ($cell) {
                        $cell->setValue('NO');
                    });
                    $sheet->cell('B2', function ($cell) {
                        $cell->setValue('WAREHOUSE');
                    });
                    $sheet->cell('C2', function ($cell) {
                        $cell->setValue('REFERENCE');
                    });
                    $sheet->cell('D2', function ($cell) {
                        $cell->setValue('DATE');
                    });

                    $sheet->cell('E2', function ($cell) {
                        $cell->setValue('STOCK');
                    });

                    $sheet->cell('E3', function ($cell) use ($data) {
                        $cell->setValue('(' .date_format_view($data['date_from']) .') - (' .date_format_view($data['date_to']). ')');
                    });

                    $sheet->cell('F2', function ($cell) {
                        $cell->setValue('ACCUMULATION STOCK');
                    });

                    $opening_inventory = Inventory::where('item_id', '=', $item->id)
                            ->where('form_date', '<', $data['date_from'])
                            ->where(function ($query) use ($data) {
                                if ($data['warehouse']) {
                                    $query->where('warehouse_id', '=', $data['warehouse']);
                                }
                            })
                            ->orderBy('form_date', '=', 'desc')
                            ->first();
                    $total_quantity = $opening_inventory ? $opening_inventory->total_quantity : 0;

                    $content = [];
                    $total_data = count($data['list_inventory']);
                    array_push($content, [1, '-', 'OPENING STOCK', date_format_view($data['date_from']), '-', number_format_quantity($total_quantity, 0)]);
                    for ($i=0; $i < $total_data; $i++) {
                        $total_quantity += $data['list_inventory'][$i]['quantity'];
                        $formulir = Formulir::find($data['list_inventory'][$i]['formulir_id']);
                        $warehouse = Warehouse::find($data['list_inventory'][$i]['warehouse_id']);
                        array_push($content, [$i + 2,
                            strtoupper($warehouse->name),
                            strtoupper($formulir->form_number),
                            strtoupper(date_format_view($data['list_inventory'][$i]['form_date'])),
                            strtoupper(number_format_quantity($data['list_inventory'][$i]['quantity'], 0)),
                            strtoupper(number_format_quantity($data['list_inventory'][$i]['total_quantity'], 0))
                        ]);
                    }
                    array_push($content, [$total_data + 2, '-', 'END STOCK', date_format_view($data['date_to']), '-', number_format_quantity($total_quantity, 0)]);
                    $total_data = $total_data+5;
                    $sheet->fromArray($content, null, 'A4', false, false);
                    $sheet->setBorder('A2:F'.$total_data, 'thin');
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$request->project->url.'/inventory-report-detail/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $data) {
            QueueHelper::reconnectAppDatabase($data['request']['database_name']);
            \Mail::send('framework::email.inventory-report-detail', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('INVENTORY REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}

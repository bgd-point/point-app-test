<?php

namespace Point\Framework\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Point\Framework\Helpers\WarehouseHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;

class InventoryValueReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        WarehouseHelper::isAvailable();

        $array_of_search = explode(' ',\Input::get('search'));
        $view = view('framework::app.inventory.value-report.index');
        $view->list_warehouse = Warehouse::active()->get();
        $view->search_warehouse = \Input::get('warehouse_id') ? Warehouse::find(\Input::get('warehouse_id')) : 0;
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        $view->inventory = Inventory::joinItem()
            ->groupBy('inventory.item_id')
            ->where('inventory.total_quantity', '>', 0)
            ->where(function ($query) use ($view, $array_of_search) {
                if ($view->search_warehouse) {
                    $query->where('inventory.warehouse_id', $view->search_warehouse->id);
                }
                foreach ($array_of_search as $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('item.name', 'like', '%'.$search.'%')
                            ->orWhere('item.code', 'like', '%'.$search.'%')
                            ->orWhere('item.notes', 'like', '%'.$search.'%');
                    });
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
        $view = view('framework::app.inventory.value-report.detail');
        $view->date_from = \Input::get('date_from') ? \Input::get('date_from') : $date_from;
        $view->date_to = \Input::get('date_to') ? \Input::get('date_to') : $date_to;
        $view->warehouse = Warehouse::find($warehouse_id);
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
            ->orderBy('formulir_id', 'asc')
            ->paginate(100);

        return $view;
    }


    /**
     * Export inventory value report to excel
     *
     * @return json
     */
    public function export(Request $request)
    {
        access_is_allowed('export.inventory.value.report');
        $storage = storage_path('app/'.$request->project->url.'/inventory-value-report/');
        $fileName = 'INVENTORY VALUE REPORT '.date('YmdHis');
        $cRequest = $request;
        $request = $request->input();
        // return Response()->json($request);
        \Queue::push(function ($job) use ($request, $fileName, $storage) {
            \Excel::create($fileName, function ($excel) use ($storage, $request) {
                // Sheet Data
                $excel->sheet('Data', function ($sheet) use ($request) {
                    $sheet->setWidth(array(
                        'A' => 30,
                        'B' => 15,
                        'C' => 20,
                        'D' => 15,
                        'E' => 20,
                        'F' => 15,
                        'G' => 20,
                        'H' => 15,
                        'I' => 20,
                    ));

                    // Set Header Style
                    $sheet->mergeCells('A1:I1', 'center');
                    $sheet->mergeCells('A2:A3');
                    $sheet->mergeCells('B2:C2', 'center');
                    $sheet->mergeCells('D2:E2', 'center');
                    $sheet->mergeCells('F2:G2', 'center');
                    $sheet->mergeCells('H2:I2', 'center');
                    $sheet->mergeCells('B3:C3', 'center');
                    $sheet->mergeCells('D3:E3', 'center');
                    $sheet->mergeCells('F3:G3', 'center');
                    $sheet->mergeCells('H3:I3', 'center');

                    $sheet->cell('A1:I4', function ($cell) {
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));
                        $cell->setValignment('center');
                    });
                    $sheet->cell('B2:I3', function ($cell) {
                        $cell->setAlignment('center');
                    });
                    
                    $sheet->setCellValue('A1', 'INVENTORY VALUE REPORT');
                    $sheet->setCellValue('A2', 'ITEM');
                    $sheet->setCellValue('B2', 'OPENING STOCK');
                    $sheet->setCellValue('D2', 'STOCK IN');
                    $sheet->setCellValue('F2', 'STOCK OUT');
                    $sheet->setCellValue('H2', 'CLOSING STOCK');
                    
                    $date_from = date_format_db($request['date_from']) ?: date('Y-m-01 00:00:00');
                    $date_to = date_format_db($request['date_to'], 'end') ?: date('Y-m-d 23:59:59');
                    
                    $sheet->setCellValue('B3', '(' . date_format_view($date_from) . ')');
                    $sheet->setCellValue('D3', '(' . date_format_view($date_from) . ')-(' . date_format_view($date_to) . ')');
                    $sheet->setCellValue('F3', '(' . date_format_view($date_from) . ')-(' . date_format_view($date_to) . ')');
                    $sheet->setCellValue('H3', '(' . date_format_view($date_to) . ')');

                    $sheet->cell('B3:I3', function ($cell) {
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '10',
                            'bold'       =>  true
                        ));
                    });

                    $sheet->setCellValue('B4', 'QTY');
                    $sheet->setCellValue('C4', 'VALUE');
                    $sheet->setCellValue('D4', 'QTY');
                    $sheet->setCellValue('E4', 'VALUE');
                    $sheet->setCellValue('F4', 'QTY');
                    $sheet->setCellValue('G4', 'VALUE');
                    $sheet->setCellValue('H4', 'QTY');
                    $sheet->setCellValue('I4', 'VALUE');
                    
                    $warehouse = $request['warehouse'] ? : 0;
                    $array_of_search = explode(' ', $request['search']);
                    $list_report = Inventory::joinItem()
                        ->groupBy('inventory.item_id')
                        ->where('inventory.total_quantity', '>', 0)
                        ->where(function ($query) use ($array_of_search, $warehouse) {
                            if ($warehouse > 0) {
                                $query->where('inventory.warehouse_id', $warehouse);
                            }
                            else {
                                foreach ($array_of_search as $search) {
                                    $query->where(function ($q) use ($search) {
                                        $q->where('item.name', 'like', '%'.$search.'%')
                                            ->orWhere('item.code', 'like', '%'.$search.'%')
                                            ->orWhere('item.notes', 'like', '%'.$search.'%');
                                    });
                                }
                            }
                        })
                        ->where(function ($query) use ($date_from, $date_to) {
                            $query->whereBetween('inventory.form_date', [$date_from, $date_to])
                                ->orWhere('inventory.form_date', '<', $date_from);
                        })
                        ->paginate(100);

                    $content = array();
                    $total_closing_value = 0;
                    foreach ($list_report as $report) {
                        if ($warehouse) {
                            $opening_stock = inventory_get_opening_stock($date_from, $report->item_id, $warehouse);
                            $opening_value = inventory_get_opening_value($date_from, $report->item_id, $warehouse);
                            $stock_in = inventory_get_stock_in($date_from, $date_to, $report->item_id, $warehouse);
                            $value_in = inventory_get_value_in($date_from, $date_to, $report->item_id, $warehouse);
                            $stock_out = inventory_get_stock_out($date_from, $date_to, $report->item_id, $warehouse);
                            $value_out = inventory_get_value_out($date_from, $date_to, $report->item_id, $warehouse);
                            $closing_stock = inventory_get_closing_stock($date_from, $date_to, $report->item_id, $warehouse);
                            $closing_value = inventory_get_closing_value($date_from, $date_to, $report->item_id, $warehouse);
                        } else {
                            $opening_stock = inventory_get_opening_stock_all($date_from, $report->item_id);
                            $opening_value = inventory_get_opening_value_all($date_from, $report->item_id);
                            $stock_in = inventory_get_stock_in_all($date_from, $date_to, $report->item_id);
                            $value_in = inventory_get_value_in_all($date_from, $date_to, $report->item_id);
                            $stock_out = inventory_get_stock_out_all($date_from, $date_to, $report->item_id);
                            $value_out = inventory_get_value_out_all($date_from, $date_to, $report->item_id);
                            $closing_stock = inventory_get_closing_stock_all($date_from, $date_to, $report->item_id);
                            $closing_value = inventory_get_closing_value_all($date_from, $date_to, $report->item_id);
                        }
                        $total_closing_value += $closing_value;

                        array_push($content, [
                            $report->item->codeName,
                            number_format_quantity($opening_stock),
                            number_format_quantity($opening_value),
                            number_format_quantity($stock_in),
                            number_format_quantity($value_in),
                            number_format_quantity($stock_out),
                            number_format_quantity($value_out),
                            number_format_quantity($closing_stock),
                            number_format_quantity($closing_value)
                        ]);
                    }
                    // prints all list report into excel sheet
                    $sheet->fromArray($content, null, 'A5', false, false);
                    
                    // get end row
                    $end_row = $list_report->count()+5;

                    //set table border
                    $sheet->setBorder('A2:I'.$end_row, 'thin');
                    $sheet->cell('I'.$end_row, function ($cell) use ($total_closing_value) {
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));
                        $cell->setValue(number_format_quantity($total_closing_value));
                    });
                    $sheet->setBorder('I'.$end_row, 'thin');

                // LEFT ALIGNMENT FOR COLUMN B TO I FROM ROW 5 TO END ROW
                    $sheet->cell('B4:I'.$end_row, function($cell) {
                        $cell->setAlignment('right');
                    });
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$cRequest->project->url.'/inventory-value-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $request) {
            \Mail::send('point-purchasing::emails.purchasing.point.external.purchasing-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('INVENTORY VALUE REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}

<?php

namespace Point\Framework\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Point\Framework\Helpers\WarehouseHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;
use Point\Framework\Models\Master\Coa;
use Psy\Util\Json;

class InventoryValueReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Point\Core\Exceptions\PointException
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
        $view->list_coa = Coa::getByCategory('inventories');
        $view->coa = \Input::get('account') ?: 0;
        $view->inventory = Inventory::joinItem()
            ->groupBy('inventory.item_id')
            ->where('inventory.total_quantity', '>', 0)
            ->where(function ($query) use ($view, $array_of_search) {
                if ($view->search_warehouse) {
                    $query->where('inventory.warehouse_id', $view->search_warehouse->id);
                }
                if ($view->coa > 0) {
                    $query->where('account_asset_id', $view->coa);
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
            ->orderBy('id', 'asc')
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

        \Queue::push(function ($job) use ($request, $fileName, $storage) {
            \Excel::create($fileName, function ($excel) use ($storage, $request) {
                // Sheet Data
                $warehouse = $request['warehouse'] ? : 0;

                if ($warehouse == 0) {
                    info('warehouse ' . $warehouse);
                    $whAll = Warehouse::all();
                    foreach ($whAll as $wh) {
                        $excel->sheet(substr($wh->name,0, 20), function ($sheet) use ($request, $wh, $storage) {
                            $sheet->setWidth(array(
                                'A' => 30,
                                'B' => 15,
                                'C' => 20,
                                'D' => 20,
                                'E' => 15,
                                'F' => 20,
                                'G' => 15,
                                'H' => 20,
                                'I' => 15,
                                'J' => 20,
                                'K' => 20,
                                'L' => 20,
                            ));

                            // Set Header Style
                            $sheet->mergeCells('A1:L1', 'center');
                            $sheet->mergeCells('A2:A3');
                            $sheet->mergeCells('B2:B3');
                            $sheet->mergeCells('C2:E2', 'center');
                            $sheet->mergeCells('F2:G2', 'center');
                            $sheet->mergeCells('H2:I2', 'center');
                            $sheet->mergeCells('J2:L2', 'center');
                            $sheet->mergeCells('C3:E3', 'center');
                            $sheet->mergeCells('F3:G3', 'center');
                            $sheet->mergeCells('H3:I3', 'center');
                            $sheet->mergeCells('J3:L3', 'center');

                            $sheet->cell('A1:L4', function ($cell) {
                                $cell->setFont(array(
                                    'size'       => '14',
                                    'bold'       =>  true
                                ));
                                $cell->setValignment('center');
                            });

                            // Set Header Text
                            $sheet->setCellValue('A1', 'INVENTORY VALUE REPORT');
                            $sheet->setCellValue('A2', 'CODE');
                            $sheet->setCellValue('B2', 'ITEM');
                            $sheet->setCellValue('C2', 'OPENING STOCK');
                            $sheet->setCellValue('F2', 'STOCK IN');
                            $sheet->setCellValue('H2', 'STOCK OUT');
                            $sheet->setCellValue('J2', 'CLOSING STOCK');

                            $date_from = date_format_db($request['date_from']) ?: date('Y-m-01 00:00:00');
                            $date_to = date_format_db($request['date_to'], 'end') ?: date('Y-m-d 23:59:59');

                            $sheet->setCellValue('C3', '(' . date_format_view($date_from) . ')');
                            $sheet->setCellValue('F3', '(' . date_format_view($date_from) . ')-(' . date_format_view($date_to) . ')');
                            $sheet->setCellValue('H3', '(' . date_format_view($date_from) . ')-(' . date_format_view($date_to) . ')');
                            $sheet->setCellValue('J3', '(' . date_format_view($date_to) . ')');

                            $sheet->cell('C3:L3', function ($cell) {
                                $cell->setFont(array(
                                    'size'       => '10',
                                    'bold'       =>  true
                                ));
                            });

                            $sheet->setCellValue('B4', 'QTY');
                            $sheet->setCellValue('C4', 'COST OF SALES');
                            $sheet->setCellValue('D4', 'TOTAL VALUE');
                            $sheet->setCellValue('E4', 'QTY');
                            $sheet->setCellValue('F4', 'TOTAL VALUE');
                            $sheet->setCellValue('G4', 'QTY');
                            $sheet->setCellValue('H4', 'TOTAL VALUE');
                            $sheet->setCellValue('I4', 'QTY');
                            $sheet->setCellValue('J4', 'LAST BUY PRICE');
                            $sheet->setCellValue('K4', 'TOTAL VALUE');

                            // Get inventory list
                            $warehouse = $wh->id;
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
                                ->get();

                            $content = array();
                            $total_closing_value = 0;

                            foreach ($list_report as $index=>$report) {
                                if ($warehouse) {
                                    $opening_stock = inventory_get_opening_stock($date_from, $report->item_id, $warehouse);
                                    $opening_cogs = inventory_get_cost_of_sales_value($date_from, $report->item_id, $warehouse);
                                    $opening_value = inventory_get_opening_value($date_from, $report->item_id, $warehouse);
                                    $stock_in = inventory_get_stock_in($date_from, $date_to, $report->item_id, $warehouse);
                                    $value_in = inventory_get_value_in($date_from, $date_to, $report->item_id, $warehouse);
                                    $stock_out = inventory_get_stock_out($date_from, $date_to, $report->item_id, $warehouse);
                                    $value_out = inventory_get_value_out($date_from, $date_to, $report->item_id, $warehouse);
                                    $closing_stock = inventory_get_closing_stock($date_from, $date_to, $report->item_id, $warehouse);
                                    $closing_cogs = inventory_get_cost_of_sales_value($date_to, $report->item_id, $warehouse);
                                    $closing_value = inventory_get_closing_value($date_from, $date_to, $report->item_id, $warehouse);
                                } else {
                                    $opening_stock = inventory_get_opening_stock_all($date_from, $report->item_id);
                                    $opening_cogs = inventory_get_cost_of_sales_value_all($date_from, $report->item_id);
                                    $opening_value = inventory_get_opening_value_all($date_from, $report->item_id);
                                    $stock_in = inventory_get_stock_in_all($date_from, $date_to, $report->item_id);
                                    $value_in = inventory_get_value_in_all($date_from, $date_to, $report->item_id);
                                    $stock_out = inventory_get_stock_out_all($date_from, $date_to, $report->item_id);
                                    $value_out = inventory_get_value_out_all($date_from, $date_to, $report->item_id);
                                    $closing_stock = inventory_get_closing_stock_all($date_from, $date_to, $report->item_id);
                                    $closing_cogs = inventory_get_cost_of_sales_value_all($date_to, $report->item_id);;
                                    $closing_value = inventory_get_closing_value_all($date_from, $date_to, $report->item_id);
                                }


                                $item = $report;

                                $lastBuy = \Point\PointPurchasing\Models\Inventory\InvoiceItem::join('point_purchasing_invoice', 'point_purchasing_invoice.id', '=', 'point_purchasing_invoice_item.point_purchasing_invoice_id')
                                    ->join('formulir', 'point_purchasing_invoice.formulir_id', '=', 'formulir.id')
                                    ->where('point_purchasing_invoice_item.item_id', '=', $item->item_id)
                                    ->where('formulir.form_date', '<=', $date_to)
                                    ->orderBy('formulir.form_date', 'desc')
                                    ->first();

                                $price = 0;

                                if ($lastBuy) {
                                    $price = $lastBuy->price;
                                } else {
                                    $ci = \Point\PointAccounting\Models\CutOffInventoryDetail::where('subledger_id', $item->item_id)->first();

                                    if ($ci) {
                                        $price = $ci->amount / $ci->stock;
                                    } else {
                                        $product = \Point\PointManufacture\Models\InputProduct::join('point_manufacture_input', 'point_manufacture_input.id', '=', 'point_manufacture_input_product.input_id')
                                            ->join('formulir', 'point_manufacture_input.formulir_id', '=', 'formulir.id')
                                            ->where('formulir.form_date', '<=', request()->get('date_to') ?? \Carbon\Carbon::now())
                                            ->whereNotNull('formulir.form_number')
                                            ->where('formulir.form_status', '!=', -1)
                                            ->where('product_id', $item->item_id)
                                            ->first();
                                        if ($product) {
                                            $materials = \Point\PointManufacture\Models\InputMaterial::where('input_id', $product->input_id)->get();
                                            $price = 0;
                                            $outputProduct = \Point\PointManufacture\Models\OutputProduct::join('point_manufacture_output', 'point_manufacture_output.id', '=', 'point_manufacture_output_product.output_id')
                                                ->where('point_manufacture_output.input_id', $product->input_id)
                                                ->first();
                                            foreach ($materials as $material) {
                                                $lastBuyMaterial = \Point\PointPurchasing\Models\Inventory\InvoiceItem::join('point_purchasing_invoice', 'point_purchasing_invoice.id', '=', 'point_purchasing_invoice_item.point_purchasing_invoice_id')
                                                    ->join('formulir', 'point_purchasing_invoice.formulir_id', '=', 'formulir.id')
                                                    ->where('point_purchasing_invoice_item.item_id', '=', $material->material_id)
                                                    ->where('formulir.form_date', '<=', $date_to)
                                                    ->whereNotNull('formulir.form_number')
                                                    ->orderBy('formulir.form_date', 'desc')
                                                    ->first();

                                                if ($lastBuyMaterial && $outputProduct) {
                                                    $price += ($material->quantity * $lastBuyMaterial->price) / $outputProduct->quantity;
                                                }
                                            }
                                        }
                                        else {
                                            $oi = \Point\Framework\Models\OpeningInventory::where('item_id', '=', $item->item_id)->first();
                                            if ($oi) {
                                                $price = $oi->price;
                                            }
                                        }
                                    }
                                }

                                $total_closing_value += ($closing_stock * $price);

                                // Store each report in array
                                array_push($content, [
                                    $report->item->code,
                                    $report->item->name,
                                    number_format($opening_stock, 0),
                                    number_format($opening_cogs, 0),
                                    number_format($opening_value, 0),
                                    number_format($stock_in, 0),
                                    number_format($value_in, 0),
                                    number_format($stock_out, 0),
                                    number_format($value_out, 0),
                                    number_format($closing_stock, 0),
                                    number_format($price, 0),
                                    number_format($closing_stock * $price, 0)
                                ]);

                                // If item needs recalculate stock
                                if($report->recalculate === 1) {
                                    $sheet->cell('A'.($index+5), function($cell) {
                                        $cell->setFont(array(
                                            'bold' => true,
                                            'underline' => true
                                        ));
                                        $cell->setFontColor('#F00');
                                    });
                                }
                            }
                            // Prints all list report into excel sheet
                            $sheet->fromArray($content, null, 'A5', false, false);

                            $end_row = $list_report->count()+5;

                            // Set table border
                            $sheet->setBorder('A2:K'.$end_row, 'thin');
                            $sheet->cell('K'.$end_row, function ($cell) use ($total_closing_value) {
                                $cell->setFontWeight(true);
                                $cell->setValue(number_format($total_closing_value, 0));
                            });
                            $sheet->setBorder('I'.$end_row, 'thin');

                            // Right alignment for cells with number
                            $sheet->cell('B4:K'.$end_row, function($cell) {
                                $cell->setAlignment('right');
                            });
                        });
                    }
                } else {
                    $excel->sheet('Data', function ($sheet) use ($request) {
                        $sheet->setWidth(array(
                            'A' => 30,
                            'B' => 15,
                            'C' => 20,
                            'D' => 20,
                            'E' => 15,
                            'F' => 20,
                            'G' => 15,
                            'H' => 20,
                            'I' => 15,
                            'J' => 20,
                            'K' => 20,
                        ));

                        // Set Header Style
                        $sheet->mergeCells('A1:K1', 'center');
                        $sheet->mergeCells('A2:A3');
                        $sheet->mergeCells('B2:D2', 'center');
                        $sheet->mergeCells('E2:F2', 'center');
                        $sheet->mergeCells('G2:H2', 'center');
                        $sheet->mergeCells('I2:K2', 'center');
                        $sheet->mergeCells('B3:D3', 'center');
                        $sheet->mergeCells('E3:F3', 'center');
                        $sheet->mergeCells('G3:H3', 'center');
                        $sheet->mergeCells('I3:K3', 'center');

                        $sheet->cell('A1:K4', function ($cell) {
                            $cell->setFont(array(
                                'size'       => '14',
                                'bold'       =>  true
                            ));
                            $cell->setValignment('center');
                        });

                        // Set Header Text
                        $sheet->setCellValue('A1', 'INVENTORY VALUE REPORT');
                        $sheet->setCellValue('A2', 'CODE');
                        $sheet->setCellValue('B2', 'ITEM');
                        $sheet->setCellValue('C2', 'OPENING STOCK');
                        $sheet->setCellValue('E2', 'STOCK IN');
                        $sheet->setCellValue('H2', 'STOCK OUT');
                        $sheet->setCellValue('J2', 'CLOSING STOCK');

                        $date_from = date_format_db($request['date_from']) ?: date('Y-m-01 00:00:00');
                        $date_to = date_format_db($request['date_to'], 'end') ?: date('Y-m-d 23:59:59');

                        $sheet->setCellValue('C3', '(' . date_format_view($date_from) . ')');
                        $sheet->setCellValue('F3', '(' . date_format_view($date_from) . ')-(' . date_format_view($date_to) . ')');
                        $sheet->setCellValue('H3', '(' . date_format_view($date_from) . ')-(' . date_format_view($date_to) . ')');
                        $sheet->setCellValue('J3', '(' . date_format_view($date_to) . ')');

                        $sheet->cell('B3:K3', function ($cell) {
                            $cell->setFont(array(
                                'size'       => '10',
                                'bold'       =>  true
                            ));
                        });

                        $sheet->setCellValue('B4', 'QTY');
                        $sheet->setCellValue('C4', 'COST OF SALES');
                        $sheet->setCellValue('D4', 'TOTAL VALUE');
                        $sheet->setCellValue('E4', 'QTY');
                        $sheet->setCellValue('F4', 'TOTAL VALUE');
                        $sheet->setCellValue('G4', 'QTY');
                        $sheet->setCellValue('H4', 'TOTAL VALUE');
                        $sheet->setCellValue('I4', 'QTY');
                        $sheet->setCellValue('J4', 'COST OF SALES');
                        $sheet->setCellValue('K4', 'TOTAL VALUE');

                        // Get inventory list
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
                            ->get();

                        $content = array();
                        $total_closing_value = 0;

                        foreach ($list_report as $index=>$report) {
                            if ($warehouse) {
                                $opening_stock = inventory_get_opening_stock($date_from, $report->item_id, $warehouse);
                                $opening_cogs = inventory_get_cost_of_sales_value($date_from, $report->item_id, $warehouse);
                                $opening_value = inventory_get_opening_value($date_from, $report->item_id, $warehouse);
                                $stock_in = inventory_get_stock_in($date_from, $date_to, $report->item_id, $warehouse);
                                $value_in = inventory_get_value_in($date_from, $date_to, $report->item_id, $warehouse);
                                $stock_out = inventory_get_stock_out($date_from, $date_to, $report->item_id, $warehouse);
                                $value_out = inventory_get_value_out($date_from, $date_to, $report->item_id, $warehouse);
                                $closing_stock = inventory_get_closing_stock($date_from, $date_to, $report->item_id, $warehouse);
                                $closing_cogs = inventory_get_cost_of_sales_value($date_to, $report->item_id, $warehouse);
                                $closing_value = inventory_get_closing_value($date_from, $date_to, $report->item_id, $warehouse);
                            } else {
                                $opening_stock = inventory_get_opening_stock_all($date_from, $report->item_id);
                                $opening_cogs = inventory_get_cost_of_sales_value_all($date_from, $report->item_id);
                                $opening_value = inventory_get_opening_value_all($date_from, $report->item_id);
                                $stock_in = inventory_get_stock_in_all($date_from, $date_to, $report->item_id);
                                $value_in = inventory_get_value_in_all($date_from, $date_to, $report->item_id);
                                $stock_out = inventory_get_stock_out_all($date_from, $date_to, $report->item_id);
                                $value_out = inventory_get_value_out_all($date_from, $date_to, $report->item_id);
                                $closing_stock = inventory_get_closing_stock_all($date_from, $date_to, $report->item_id);
                                $closing_cogs = inventory_get_cost_of_sales_value_all($date_to, $report->item_id);;
                                $closing_value = inventory_get_closing_value_all($date_from, $date_to, $report->item_id);
                            }

                            // Store each report in array
                            array_push($content, [
                                $report->item->code,
                                $report->item->name,
                                number_format($opening_stock, 0),
                                number_format($opening_cogs, 0),
                                number_format($opening_value, 0),
                                number_format($stock_in, 0),
                                number_format($value_in, 0),
                                number_format($stock_out, 0),
                                number_format($value_out, 0),
                                number_format($closing_stock, 0),
                                number_format($closing_cogs, 0),
                                number_format($closing_value, 0)
                            ]);

                            // If item needs recalculate stock
                            if($report->recalculate === 1) {
                                $sheet->cell('A'.($index+5), function($cell) {
                                    $cell->setFont(array(
                                        'bold' => true,
                                        'underline' => true
                                    ));
                                    $cell->setFontColor('#F00');
                                });
                            }
                        }
                        // Prints all list report into excel sheet
                        $sheet->fromArray($content, null, 'A5', false, false);

                        $end_row = $list_report->count()+5;

                        // Set table border
                        $sheet->setBorder('A2:K'.$end_row, 'thin');
                        $sheet->cell('K'.$end_row, function ($cell) use ($total_closing_value) {
                            $cell->setFontWeight(true);
                            $cell->setValue(number_format($total_closing_value, 0));
                        });
                        $sheet->setBorder('I'.$end_row, 'thin');

                        // Right alignment for cells with number
                        $sheet->cell('B4:K'.$end_row, function($cell) {
                            $cell->setAlignment('right');
                        });
                    });
                }

            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$cRequest->project->url.'/inventory-value-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $request) {
            \Mail::send('framework::email.inventory-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('INVENTORY VALUE REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        return response()->json();
    }
}

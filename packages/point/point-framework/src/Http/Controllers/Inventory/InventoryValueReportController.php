<?php

namespace Point\Framework\Http\Controllers\Inventory;

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
        \Queue::push(function ($job) use ($request, $fileName, $storage) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Excel::create($fileName, function ($excel) use ($storage, $request) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($request) {
                    $sheet->setWidth(array(
                        'A' => 10,
                        'B' => 25,
                        'C' => 25,
                        'D' => 25,
                        'E' => 25,
                        'F' => 25,
                        'G' => 25,
                        'H' => 25
                    ));

                    // MERGER COLUMN
                    $sheet->mergeCells('A1:H1', 'center');
                    $sheet->cell('A1:H2', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));
                    });

                    $sheet->cell('A1', function ($cell) {
                        $cell->setValue(strtoupper('INVENTORY VALUE REPORT'));
                    });
                    >>>>>>> SAMPAI SINI KERJAKAN NYA
                    $list_report = PurchaseReportHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
                    $content = array(array('FORM DATE', 'FORM NUMBER', 'SUPPLIER', 'ITEM', 'QUANTITY', 'UNIT', 'PRICE', 'TOTAL'));
                    $total_value = 0;
                    foreach ($list_report as $report) {
                        $total = $report->quantity * $report->price;
                        $total_value += $total;
                        array_push($content, [
                            date_format_view($report->invoice->formulir->form_date),
                            $report->invoice->formulir->form_number,
                            $report->invoice->supplier->codeName,
                            $report->item->codeName,
                            number_format_quantity($report->quantity, 0),
                            $report->unit,
                            number_format_quantity($report->price),
                            number_format_quantity($total)
                        ]);
                    }
                    $total_data = $list_report->count()+2;
                    $sheet->fromArray($content, null, 'A2', false, false);
                    $sheet->setBorder('A2:H'.$total_data, 'thin');
                    $next_row = $total_data + 1;
                    $sheet->cell('H'.$next_row, function ($cell) use ($total_value) {
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));
                        $cell->setValue(number_format_quantity($total_value));
                    });
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$cRequest->project->url.'/purchasing-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-purchasing::emails.purchasing.point.external.purchasing-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('PURCHASE REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}

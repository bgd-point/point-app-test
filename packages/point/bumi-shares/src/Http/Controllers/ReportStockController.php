<?php

namespace Point\BumiShares\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\BumiShares\Helpers\SharesHelper;
use Point\BumiShares\Models\Broker;
use Point\BumiShares\Models\Buy;
use Point\BumiShares\Models\OwnerGroup;
use Point\BumiShares\Models\SellingPrice;
use Point\BumiShares\Models\Shares;
use Point\BumiShares\Models\Stock;
use Point\BumiShares\Models\StockFifo;
use Point\Core\Helpers\QueueHelper;

class ReportStockController extends Controller
{
    public function index()
    {
        access_is_allowed('read.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.index');
        $view->list_owner_group = OwnerGroup::active()->get();
        $view->list_shares = Shares::active()->get();

        $view->group = app('request')->input('group_id') ? OwnerGroup::find(app('request')->input('group_id')) : '';
        $view->shares = app('request')->input('shares_id') ? Shares::find(app('request')->input('shares_id')) : '';
        $view->list_stock_shares = Stock::where('remaining_quantity', '>', 0);

        if (app('request')->input('group_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('owner_group_id', '=', app('request')->input('group_id'));
        }

        if (app('request')->input('shares_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('shares_id', '=', app('request')->input('shares_id'));
        }

        $view->list_stock_shares = $view->list_stock_shares->orderBy('shares_id');

        $view->total_quantity = 0;
        $view->total_value = 0;
        $view->total_selling = 0;
        $view->estimation_of_selling_value = 0;
        $view->estimation_of_profit_and_loss = 0;
        return $view;
    }

    public function detail($formulir_id, $shares_id)
    {
        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.detail');
        $view->list_stock_fifo = StockFifo::joinFormulirSell()->where('shares_in_id', $formulir_id)->where('quantity', '>', 0)->get();
        $view->buy = Buy::where('formulir_id', $formulir_id)->first();
        $view->shares = Shares::find($shares_id);
        return $view;
    }

    public function export(Request $request)
    {
        $cRequest = $request;
        $storage = storage_path('app/'.$request->project->url.'/shares-stock-report/');
        $request = $request->input();
        $fileName = 'shares stock report '.date('YmdHis');
        \Queue::push(function ($job) use ($fileName, $storage, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Excel::create($fileName, function ($excel) use ($storage, $request) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($request) {
                    // MERGER COLUMN
                    $sheet->mergeCells('A1:N1', 'center');
                    $sheet->cell('A1', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));


                        $cell->setValue(strtoupper('SHARES STOCK REPORT'));
                    });

                    $sheet->cell('A2:N2', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    $data = SharesHelper::searchReportStock($request['shares_id'], $request['group_id'])->get()->toArray();

                    $content = array(array(
                        'NO', 'SHARES NAME', 'PURCHASE DATE','PRICE', 'EX SALE',
                        'QUANTITY', 'TOTAL + FEE', 'TOTAL PRICE', 'TOTAL QUANTITY', 'AVERAGE PRICE',
                        'PRICE OF SALE', 'TOTAL + FEE', 'PROFIT/LOST', 'BROKER'));

                    $total_data = count($data);
                    $no = 1;
                    $total_sell_fee = 0;
                    for($i=0; $i<$total_data; $i++) {
                        $first = Stock::where('shares_id', $data[$i]['shares_id'])
                            ->selectRaw('sum(quantity) as total_quantity, sum(price) as total_price')
                            ->first();
                        $fifo = StockFifo::where('shares_in_id', $data[$i]['formulir_id'])->first();
                        $broker = Broker::find($data[$i]['broker_id']);
                        $shares = Shares::find($data[$i]['shares_id']);
                        if ($fifo) {
                            $total_sell_fee = $first->total_quantity *  $fifo->price + $first->total_quantity *  $fifo->price * $data[$i]['fee'] /100;
                        }
                        array_push($content, [
                            $no,
                            $shares->name,
                            date_format_view($data[$i]['date']),
                            number_format_quantity($data[$i]['price']),
                            number_format_quantity($data[$i]['average_price']),
                            number_format_quantity($data[$i]['quantity']),
                            number_format_quantity($data[$i]['quantity'] * $data[$i]['price'] + $data[$i]['quantity'] * $data[$i]['price'] * $data[$i]['fee'] / 100),
                            number_format_quantity($first->total_price * $first->total_quantity),
                            number_format_quantity($first->total_quantity),
                            number_format_quantity($first->total_price * $first->total_quantity / $first->total_quantity),
                            $fifo ? $fifo->price : 0,
                            $fifo ? number_format_quantity($total_sell_fee) : 0,
                            $fifo ? number_format_quantity($total_sell_fee - $first->total_price) : 0,
                            $broker->name
                        ]);

                        $no++;
                    }
                    
                    $total_data = $total_data + 2;
                    $sheet->fromArray($content, null, 'A2', false, false);
                    $sheet->setBorder('A2:N'.$total_data, 'thin');
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$cRequest->project->url.'/shares-stock-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('bumi-shares::emails.facility.bumi-shares.external.shares-stock-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('SHARES STOCK REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }

    public function printReport()
    {
        access_is_allowed('export.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.print');
        $view->list_owner_group = OwnerGroup::active()->get();
        $view->list_shares = Shares::active()->get();

        $view->group = app('request')->input('group_id') ? OwnerGroup::find(app('request')->input('group_id')) : '';
        $view->shares = app('request')->input('shares_id') ? Shares::find(app('request')->input('shares_id')) : '';
        $view->list_stock_shares = Stock::where('remaining_quantity', '>', 0);

        if (app('request')->input('group_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('owner_group_id', '=', app('request')->input('group_id'));
        }

        if (app('request')->input('shares_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('shares_id', '=', app('request')->input('shares_id'));
        }

        $view->list_stock_shares = $view->list_stock_shares->orderBy('shares_id');

        $view->total_quantity = 0;
        $view->total_value = 0;
        $view->total_selling = 0;
        $view->estimation_of_selling_value = 0;
        $view->estimation_of_profit_and_loss = 0;
        return $view;
    }

    public function estimateOfSellingPrice()
    {
        access_is_allowed('read.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.estimate-of-selling-price');
        $view->list_stock_shares = Shares::active()->get();
        return $view;
    }

    public function updateEstimateOfSellingPrice()
    {
        access_is_allowed('read.bumi.shares.report');

        DB::beginTransaction();

        $shares_id = app('request')->input('shares_id');
        $price = app('request')->input('price');

        for ($i=0;$i<count(app('request')->input('price'));$i++) {
            $old_estimation = SellingPrice::where('shares_id', '=', $shares_id[$i])->first();

            if ($old_estimation) {
                $old_estimation->price = \NumberHelper::formatDB($price[$i]);
                $old_estimation->save();
            } else {
                $estimation = new SellingPrice;
                $estimation->price = \NumberHelper::formatDB($price[$i]);
                $estimation->shares_id = $shares_id[$i];
                $estimation->updated_by = \Auth::user()->id;
                $estimation->save();
            }
        }

        DB::commit();

        gritter_success('update selling price success');
        return redirect('facility/bumi-shares/report/stock');
    }

    public function detailExport($formulir_id, $shares_id)
    {
        $file_name = 'Shares Mutation '.auth()->user()->id . '' . date('Y-m-d_His');
        \Excel::create($file_name, function($excel) use ($formulir_id, $shares_id) {
            $excel->sheet('Shares Stock Report', function($sheet) use ($formulir_id, $shares_id) {
                $data = array(
                    'list_stock_fifo' => StockFifo::joinFormulirSell()->where('shares_in_id', $formulir_id)->where('quantity', '>', 0)->get(),
                    'buy' => Buy::where('formulir_id', $formulir_id)->first(),
                    'shares' => Shares::find($shares_id)
                 );
                
                $sheet->loadView('bumi-shares::app.facility.bumi-shares.report.stock._data-detail', $data);
            });

        })->export('xls');
    }
}

<?php

namespace Point\BumiShares\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\BumiShares\Helpers\SharesHelper;
use Point\BumiShares\Models\Buy;
use Point\BumiShares\Models\Sell;
use Point\BumiShares\Models\Shares;
use Point\BumiShares\Models\StockFifo;
use Point\Core\Helpers\QueueHelper;

class ReportSellController extends Controller
{
	public function index()
    {
        access_is_allowed('read.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.sell.index');
        $view->list_shares = Shares::active()->get();

        $view->list_stock_fifo = SharesHelper::searchSellReport(\Input::get('date_from'), \Input::get('date_to'), \Input::get('shares_id'))->paginate(100);
        $view->shares = app('request')->input('shares_id') ? Shares::find(app('request')->input('shares_id')) : '';
        return $view;
    }

    public function export(Request $request)
    {
    	$cRequest = $request;
    	$storage = storage_path('app/'.$request->project->url.'/shares-report/');
    	$request = $request->input();
        $fileName = 'shares report '.date('YmdHis');
        \Queue::push(function ($job) use ($fileName, $storage, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Excel::create($fileName, function ($excel) use ($storage, $request) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($request) {
                    // MERGER COLUMN
                    $sheet->mergeCells('A1:L1', 'center');
                    $sheet->cell('A1', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));


                        $cell->setValue(strtoupper('SHARES REPORT'));
                    });

                    $sheet->cell('A2:L2', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
        			$data = SharesHelper::searchSellReport($request['date_from'], $request['date_to'], $request['shares_id'])->get()->toArray();
                    $content = array(array('NO', 'SHARES NAME', 'PURCHASE DATE', 'QUANTITY', 'EX SALE', 'NOMINAL PURCHASE', 'BROKER', 'SALE DATE','QUANTITY', 'PRICE', 'TOTAL + FEE', 'PROFIT/LOST'));
                    $total_data = count($data);
                    $no = 1;
                    for($i=0; $i<$total_data; $i++) {
                    	$sell = Sell::where('formulir_id', $data[$i]['shares_out_id'])->first();
                        $buy = Buy::where('formulir_id', $data[$i]['shares_in_id'])->first();
                        if (!$data[$i]['quantity']) {
                            continue;
                        }

                        $total_plus_fee = $sell->price * $sell->quantity + ($sell->price * $sell->quantity * $sell->fee / 100);
                        
                    	 array_push($content, [
                    	 	$no,
                        	$sell->shares->name,
                        	date_format_view($buy->formulir->form_date),
                        	number_format_quantity($data[$i]['quantity']),
                        	number_format_quantity($data[$i]['average_price']),
                        	number_format_quantity($data[$i]['quantity'] * $buy->price),
                        	$sell->broker->name,
                        	date_format_view($sell->formulir->form_date),
                        	number_format_quantity($data[$i]['quantity']),
                        	number_format_quantity($sell->price),
                        	number_format_quantity($total_plus_fee),
                        	number_format_quantity($total_plus_fee - $data[$i]['quantity'] * $buy->price)
                    	]);

                    	$no++;
                    }
                    
                    $total_data = $total_data;
                    $sheet->fromArray($content, null, 'A2', false, false);
                    $sheet->setBorder('A2:L'.$total_data, 'thin');
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$cRequest->project->url.'/shares-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('bumi-shares::emails.facility.bumi-shares.external.shares-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('SHARES REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}
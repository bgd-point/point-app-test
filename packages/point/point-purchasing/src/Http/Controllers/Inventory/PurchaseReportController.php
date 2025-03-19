<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\PointPurchasing\Helpers\PurchaseReportHelper;

class PurchaseReportController extends Controller
{
    public function index()
    {
        access_is_allowed('read.point.purchasing.report');

        $view = view('point-purchasing::app.purchasing.point.inventory.report.index');
        $view->list_report = PurchaseReportHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->paginate(100);

        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.purchasing.report');
        $list_report = PurchaseReportHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-purchasing::app.purchasing.point.inventory.report.index-pdf', ['list_report' => $list_report])->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        
        return $pdf->stream();
    }

    public function export(Request $request)
    {
        access_is_allowed('export.point.purchasing.report');
        $storage = storage_path('app/'.$request->project->url.'/purchasing-report/');
        $fileName = 'PURCHASE REPORT '.date('YmdHis');
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
                        $cell->setValue(strtoupper('PURCHASING REPORT'));
                    });

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
                            number_format_db(number_format_quantity($report->quantity, 0)),
                            $report->unit,
                            number_format_db(number_format_quantity($report->price, 0)),
                            number_format_db($total)
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
                        $cell->setValue(number_format_db($total_value));
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

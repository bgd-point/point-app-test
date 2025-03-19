<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\PointSales\Helpers\SalesReportHelper;

class SalesReportController extends Controller
{
    public function index()
    {
        access_is_allowed('read.point.sales.report');

        $view = view('point-sales::app.sales.point.sales.report.index');
        $view->list_report = SalesReportHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->paginate(9999);

        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.sales.order');
        $list_report = SalesReportHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.sales.report.index-pdf', ['list_report' => $list_report])->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        
        return $pdf->stream();
    }

    public function export(Request $request)
    {
        access_is_allowed('export.point.sales.report');
        $storage = storage_path('app/'.$request->project->url.'/sales-report/');
        $fileName = 'sale report '.date('YmdHis');
        $cRequest = $request;
        $request = $request->input();
        \Queue::push(function ($job) use ($request, $fileName, $storage) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Excel::create($fileName, function ($excel) use ($storage, $request) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($request) {
                    $sheet->setColumnFormat(array(
                        'E' => '#,##0.00',
                        'G' => '#,##0.00',
                        'H' => '#,##0.00'
                    ));

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
                        $cell->setValue(strtoupper('SALES REPORT'));
                    });

                    $list_report = SalesReportHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
                    $content = array(array('FORM DATE', 'FORM NUMBER', 'CUSTOMER', 'ITEM', 'QUANTITY', 'UNIT', 'PRICE', 'TOTAL'));
                    $total_value = 0;
                    foreach ($list_report as $report) {
                        $total = $report->quantity * $report->price;
                        $total_value += $total;
                        array_push($content, [
                            date_format_view($report->invoice->formulir->form_date),
                            $report->invoice->formulir->form_number,
                            $report->invoice->person->codeName,
                            $report->item->codeName,
                            $report->quantity * 1,
                            $report->unit,
                            $report->price * 1,
                            $total
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
                        $cell->setValue($total_value);
                    });
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$cRequest->project->url.'/sales-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.external.sales-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('SALES REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}

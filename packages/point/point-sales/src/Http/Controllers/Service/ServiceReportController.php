<?php

namespace Point\PointSales\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\Framework\Models\Master\Service;
use Point\PointSales\Helpers\ServiceInvoiceHelper;
use Point\PointSales\Helpers\ServiceReportHelper;
use Point\PointSales\Models\Service\Invoice;
use Point\PointSales\Models\Service\InvoiceService;

class ServiceReportController extends Controller
{
    public function index()
    {
        access_is_allowed('read.point.sales.service.report');
        $view = view('point-sales::app.sales.point.service.report');
        $view->list_service = Service::active()->paginate(100);
        
        return $view;
    }

    public function detail($service_id)
    {
        access_is_allowed('read.point.sales.service.report');

        $date_from = \Input::get('date_from');
        $date_to = \Input::get('date_to');

        $view = view('point-sales::app.sales.point.service.report-detail');
        $view->list_report = ServiceReportHelper::getDetailByService($service_id, $date_from, $date_to)->paginate(100);
        $view->service = Service::find($service_id);

        return $view;
    }

    public function export(Request $request)
    {
        access_is_allowed('export.point.sales.service.report');

        $date_from = \Input::get('date_from');
        $date_to = \Input::get('date_to');
        $storage = storage_path('app/'.$request->project->url.'/sales-service-report/');
        $file_name = strtotime(date('Y-m-d h:i:s'));
        $cRequest = $request->input();
        \Queue::push(function ($job) use ($cRequest, $file_name, $storage, $date_from, $date_to) {
            QueueHelper::reconnectAppDatabase($cRequest['database_name']);
            \Excel::create($file_name, function ($excel) use ($cRequest, $storage, $date_from, $date_to) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($date_from, $date_to) {
                    $sheet->setWidth(array(
                        'A' => 10,
                        'B' => 25,
                        'C' => 25,
                        'D' => 25,
                    ));

                    // MERGER COLUMN
                    $sheet->mergeCells('A1:D1', 'center');
                    $sheet->mergeCells('A2:D2', 'center');
                    $sheet->cell('A1', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));

                        $cell->setValue(strtoupper('Sales Service Report'));
                    });
                    $sheet->cell('A2', function ($cell) use ($date_from, $date_to) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                        $period = 'All Time';
                        if ($date_from && $date_to) {
                            $period = date_format_view($date_from);
                            if ($date_from != $date_to) {
                                $period = date_format_view($date_from) .' - '. date_format_view($date_to);
                            }
                        }

                        $cell->setValue(strtoupper('PERIOD : ' . $period));
                    });
                    $sheet->cell('A3:D3', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    $date_from = $date_from ? date_format_db($date_from, 'start') : '';
                    $date_to = $date_to ? date_format_db($date_to, 'end') : '';
                    
                    $list_service = Service::active()->get();
                    $content = array(array('NO', 'SERVICE', 'TOTAL QUANTITY', 'TOTAL AMOUNT'));

                    $total_data = count($list_service);
                    $total_price = 0;
                    $total_quantity = 0;
                    $i = 0;
                    foreach ($list_service as $service) {
                        $data = ServiceReportHelper::detailByService($service->id, $date_from, $date_to);
                        if ($data) {
                            $total_price += $data->price;
                            $total_quantity += $data->quantity;
                        }
                        array_push($content, [++$i,
                            strtoupper($service->name),
                            strtoupper(number_format_quantity($data->quantity, 0)),
                            strtoupper(number_format_quantity($data->price))
                        ]);
                    }

                    $total_data = $total_data+3;
                    $sheet->fromArray($content, null, 'A3', false, false);
                    $sheet->setBorder('A3:D'.$total_data, 'thin');

                    $next_row = $total_data + 1;
                    $sheet->cell('C'.$next_row, function ($cell) use ($total_quantity) {
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                        $cell->setValue(number_format_quantity($total_quantity, 0));
                    });
                    $sheet->cell('D'.$next_row, function ($cell) use ($total_price) {
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                        $cell->setValue(number_format_quantity($total_price));
                    });
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$request->project->url.'/sales-service-report/'.$file_name.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $cRequest) {
            QueueHelper::reconnectAppDatabase($cRequest['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.external.service-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('SERVICE SALES REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}

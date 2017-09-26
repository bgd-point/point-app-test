<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\PointPurchasing\Helpers\ServiceInvoiceHelper;
use Point\PointPurchasing\Models\Service\Invoice;

class ServiceReportController extends Controller
{
    public function index()
    {
        $view = view('point-purchasing::app.purchasing.point.service.report');
        $list_invoice = Invoice::joinFormulir()
            ->joinPerson()
            ->joinDetailService()
            ->joinService()
            ->notArchived()
            ->groupBy('point_purchasing_service_invoice.id');

        $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, FALSE, FALSE, 'report', \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);

        return $view;
    }

    public function export(Request $request)
    {
        access_is_allowed('export.point.purchasing.service.report');

        $date_from = \Input::get('date_from');
        $date_to = \Input::get('date_to');
        $search = \Input::get('search');
        $storage = storage_path('app/'.$request->project->url.'/purchasing-service-report/');
        $file_name = strtotime(date('Y-m-d h:i:s'));
        $cRequest = $request->input();
        \Queue::push(function ($job) use ($cRequest, $file_name, $storage, $date_from, $date_to, $search) {
            QueueHelper::reconnectAppDatabase($cRequest['database_name']);
            \Excel::create($file_name, function ($excel) use ($cRequest, $storage, $date_from, $date_to, $search) {
                # Sheet Data
                $excel->sheet('Data', function ($sheet) use ($date_from, $date_to, $search) {
                    $sheet->setWidth(array(
                        'A' => 10,
                        'B' => 25,
                        'C' => 25,
                        'D' => 25,
                        'E' => 25,
                    ));

                    // MERGER COLUMN
                    $sheet->mergeCells('A1:E1', 'center');
                    $sheet->mergeCells('A2:E2', 'center');
                    $sheet->cell('A1', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));

                        $cell->setValue(strtoupper('Purchasing Service Report'));
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
                    
                    $list_invoice = Invoice::joinFormulir()->joinPerson()->notArchived()->groupBy('point_purchasing_service_invoice.id');
                    $list_invoice = ServiceInvoiceHelper::searchList($list_invoice, FALSE, FALSE, 'report', $date_from, $date_to, $search)->get();
                    $content = array(array('NO', 'DATE', 'FORM NUMBER', 'SUPPLIER', 'TOTAL'));
                    $total_data = $list_invoice->count();
                    $total_price = 0;
                    $i = 0;
                    foreach ($list_invoice as $invoice) {
                        $total_price += $invoice->total;
                        array_push($content, [++$i,
                            strtoupper(date_format_view($invoice->formulir->form_date)),
                            strtoupper($invoice->formulir->form_number),
                            strtoupper($invoice->person->codeName),
                            strtoupper(number_format_quantity($invoice->total))
                        ]);                    
                    }

                    $total_data = $total_data+3;
                    $sheet->fromArray($content, null, 'A3', false, false);
                    $sheet->setBorder('A3:E'.$total_data, 'thin');

                    $next_row = $total_data + 1;
                    $sheet->cell('E'.$next_row, function ($cell) use ($total_price) {
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                        $cell->setValue(number_format_quantity($total_price, 0));
                    });
                });
            })->store('xls', $storage);

            $job->delete();
        });
        
        $data_email = [
            'username' => auth()->user()->name,
            'link' => url('download/'.$request->project->url.'/purchasing-service-report/'.$file_name.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data_email, $cRequest) {
            QueueHelper::reconnectAppDatabase($cRequest['database_name']);
            \Mail::send('point-purchasing::emails.purchasing.point.external.service-report', $data_email, function ($message) use ($data_email) {
                $message->to($data_email['email'])->subject('PURCHASING SERVICE REPORT ' . date('ymdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }
}

<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Allocation;

class AllocationReportController extends Controller
{
	public function report()
    {
        if (!auth()->user()->may('read.allocation.report')) {
            return view('core::errors.restricted');
        }

        $list_allocation_report = AllocationHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'), FALSE, TRUE);
        
        $view = view('framework::app.master.allocation.report');
        $view->list_allocation_report = $list_allocation_report->paginate(100);
        $view->list_allocation = Allocation::active()->get();
        return $view;
    }

    public function detail($id)
    {
        if (!auth()->user()->may('read.allocation.report')) {
            return view('core::errors.restricted');
        }

        $list_allocation_report = AllocationHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), FALSE, $id, FALSE);
        
        $view = view('framework::app.master.allocation.report-detail');
        $view->list_allocation_report = $list_allocation_report->paginate(100);
        $view->list_allocation = Allocation::active()->get();
        return $view;
    }

    public function export(Request $request)
    {
        $date_from =\Input::get('date_from');
        $date_to =\Input::get('date_to');
        $search =\Input::get('date_search');
        $list_allocation_report = AllocationHelper::searchList($date_from, $date_to, $search, FALSE, TRUE)->get()->toArray();
        $request = $request->input();
        
        $fileName = 'ALLOCATION REPORT '.date('YmdHis');
        $storage = public_path('allocation-report/');

        \Queue::push(function ($job) use ($list_allocation_report, $date_from, $date_to, $search, $fileName, $request, $storage) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Excel::create($fileName, function ($excel) use ($list_allocation_report, $date_from, $date_to, $search, $fileName, $request, $storage) {
                # Sheet Data All of Allocation
                $excel->sheet('REPORT', function ($sheet) use ($list_allocation_report, $date_from, $date_to, $search, $fileName, $request, $storage) {
                    $sheet->setWidth(array(
                        'A' => 10,
                        'B' => 25,
                        'C' => 25,
                    ));

                    $title = strtoupper("ALLOCATION REPORT FROM " . $date_from . " - " . $date_to);
                    $info_export = "DATE EXPORT ". \Carbon::now();
                    $sheet->cell('A1', function ($cell) use ($title) {
                        $cell->setValue($title);
                    });
                    $sheet->cell('A2', function ($cell) use ($info_export) {
                        $cell->setValue($info_export);
                    });

                    // MERGER COLUMN
                    $sheet->mergeCells('A4:C4', 'center');
                    $sheet->cell('A4', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));

                        $cell->setValue(strtoupper('ALLOCATION REPORT'));
                    });

                    $sheet->mergeCells('A5:C5', 'center');
                    $sheet->cell('A5', function ($cell) use ($date_from, $date_to) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '14',
                            'bold'       =>  true
                        ));
                        
                        if ($date_from && $date_to) {
                            $cell->setValue('PERIOD : '. strtoupper(\DateHelper::formatView(date_format_db($date_from)) . ' TO ' . \DateHelper::formatView(date_format_db($date_to))));
                        } else {
                            $cell->setValue('PERIOD : '. strtoupper(\DateHelper::formatView(\Carbon::now())));
                        }
                    });

                    $sheet->cell('A6:C6', function ($cell) {
                        // Set font
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    // Generad table of content
                    $header = array(
                        array('NO', 'ALLOCATION', 'AMOUNT')
                    );

                    $total_data = count($list_allocation_report);
                    $total_amount = 0;
                    for ($i=0; $i < $total_data; $i++) {
                        $total_amount += $list_allocation_report[$i]['amount'];
                        array_push($header, [$i + 1,
                            strtoupper(Allocation::find($list_allocation_report[$i]['allocation_id'])->name),
                            number_format_price($list_allocation_report[$i]['amount'])
                        ]);                    
                    }

                    $total_data = $total_data+6;
                    $sheet->fromArray($header, null, 'A6', false, false);
                    $sheet->setBorder('A6:C'.$total_data, 'thin');

                    $next_row = $total_data + 1;
                    $sheet->cell('B'.$next_row, function ($cell) {
                        $cell->setValue('TOTAL');
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('C'.$next_row, function ($cell) use ($total_amount) {
                        $cell->setValue(number_format_price($total_amount));
                        $cell->setFont(array(
                            'family'     => 'Times New Roman',
                            'size'       => '12'
                        ));
                    });
                });

                # Sheet Detail Allocation
                for ($i=0; $i < count($list_allocation_report); $i++) { 
                    $allocation = Allocation::find($list_allocation_report[$i]['allocation_id']);

                    $excel->sheet(strtoupper($allocation->name), function ($sheet) use ($allocation, $date_from, $date_to) {
                        $list_allocation_detail = AllocationHelper::searchList($date_from, $date_to, FALSE, $allocation->id, FALSE)->get();
                        $sheet->setWidth(array(
                            'A' => 10,
                            'B' => 25,
                            'C' => 25,
                            'D' => 25,
                            'E' => 25,
                        ));

                        // MERGER COLUMN
                        $title = strtoupper("ALLOCATION REPORT ".$allocation->name." FROM " . $date_from . " - " . $date_to);
                        $sheet->mergeCells('A1:E1', 'center');
                        $sheet->cell('A1:E2', function ($cell) {
                        // Set font
                            $cell->setFont(array(
                                'family'     => 'Times New Roman',
                                'size'       => '12',
                                'bold'       =>  true
                            ));
                        });

                        $sheet->cell('A1', function ($cell) use ($title) {
                            $cell->setValue($title);
                        });

                        $header = array(
                            array('NO', 'FORM DATE', 'FORM NUMBER', 'ALLOCATION', 'AMOUNT')
                        );

                        $total_data = $list_allocation_detail->count();
                        $total_amount = 0;
                        $i = 1;
                        foreach ($list_allocation_detail as $allocation_detail) {
                            $total_amount += $allocation_detail->amount;
                            array_push($header, [$i,
                                date_format_view($allocation_detail->formulir->form_date),
                                $allocation_detail->formulir->form_number,
                                strtoupper($allocation_detail->allocation->name),
                                number_format_quantity($allocation_detail->amount)
                            ]); 
                            $i++;                   
                        }

                        $total_data = $total_data+2;
                        $sheet->fromArray($header, null, 'A2', false, false);
                        $sheet->setBorder('A2:E'.$total_data, 'thin');

                        $next_row = $total_data + 1;
                        $sheet->cell('D'.$next_row, function ($cell) {
                            $cell->setValue('TOTAL');
                            $cell->setFont(array(
                                'family'     => 'Times New Roman',
                                'size'       => '12',
                                'bold'       =>  true
                            ));
                        });
                        $sheet->cell('E'.$next_row, function ($cell) use ($total_amount) {
                            $cell->setValue(number_format_price($total_amount));
                            $cell->setFont(array(
                                'family'     => 'Times New Roman',
                                'size'       => '12'
                            ));
                        });
                    });
                }
            })->store('xls', $storage);
            $job->delete();
        });
        
        $data = [
            'username' => auth()->user()->name,
            'link' => url('allocation-report/'.$fileName.'.xls'),
            'email' => auth()->user()->email
        ];

        \Queue::push(function ($job) use ($data, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('framework::email.allocation-report', $data, function ($message) use ($data) {
                $message->to($data['email'])->subject('ALLOCATION REPORT ' . date('YmdHi'));
            });
            $job->delete();
        });

        $response = array(
            'status' => 'success'
        );

        return response()->json($response);
    }


}
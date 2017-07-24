<?php

namespace Point\PointSales\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use Point\Core\Helpers\DateHelper;
use Point\Core\Traits\ValidationTrait;
use Point\PointSales\Helpers\PosHelper;
use Point\PointSales\Models\Pos\Pos;

class PosReportController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.pos.report');
        $list_sales = Pos::joinFormulir()
            ->joinCustomer()
            ->joinDetailItem()
            ->joinItem()
            ->notArchived()
            ->groupBy('point_sales_pos.id')
            ->selectOriginal()
            ->orderBy('point_sales_pos.id');

        $list_sales = PosHelper::searchList($list_sales, 'point_sales_pos.id', 'asc',  \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'), 1);
        $view = view('point-sales::app.sales.point.pos.report.index');
        $view->list_sales = $list_sales->paginate(100);
        return $view;
    }

    public function daily()
    {
        access_is_allowed('read.point.sales.pos.daily.report');
        $list_sales = Pos::joinFormulir()->notArchived()->selectOriginal()->showToday()->userChasier();
        $view = view('point-sales::app.sales.point.pos.report.daily');
        $view->list_sales = $list_sales->paginate(100);
        return $view;
    }

    public function exportReport()
    {
        access_is_allowed('export.point.sales.pos.report');
        $list_sales = Pos::joinFormulir()
            ->joinCustomer()
            ->joinDetailItem()
            ->joinItem()
            ->notArchived()
            ->groupBy('point_sales_pos.id')
            ->selectOriginal()
            ->orderBy('point_sales_pos.id');
        
        $date_from = \Input::get('date_from');
        $date_to = \Input::get('date_to');
        $search = \Input::get('search');

        $list_sales = PosHelper::searchList($list_sales, 'point_sales_pos.id', 'asc', $date_from, $date_to, $search, 1);
        $list_sales = $list_sales->get();

        self::generateReport($list_sales, $date_from, $date_to, $search);
    }

    public function exportDailyReport()
    {
        access_is_allowed('export.point.sales.pos.daily.report');
        $list_sales = Pos::joinFormulir()->notArchived()->selectOriginal()->showToday()->userChasier()->get();

        \Excel::create('Daily Sales Report', function ($excel) use ($list_sales) {
            # Sheet Data
            $excel->sheet('Data', function ($sheet) use ($list_sales) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                    'C' => 25,
                    'D' => 25,
                    'E' => 25,
                    'F' => 25,
                    'G' => 25,
                ));

                // MERGER COLUMN
                $sheet->mergeCells('A1:F1', 'center');
                $sheet->cell('A1', function ($cell) {
                    // Set font
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '14',
                        'bold'       =>  true
                    ));

                    $cell->setValue('DAILY SALES REPORT');
                });

                $sheet->mergeCells('A2:F2', 'center');
                $sheet->cell('A2', function ($cell) {
                    // Set font
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '14',
                        'bold'       =>  true
                    ));
                    
                    $cell->setValue(strtoupper(DateHelper::formatView(date_format_db(date('d-m-Y')))));
                });

                $sheet->cell('A3:F3', function ($cell) {
                    // Set font
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '12',
                        'bold'       =>  true
                    ));
                });

                // Generad table of content
                $header = array(
                    array('NO', 'FORM NUMBER', 'FORM DATE', 'CUSTOMER', 'SALES', 'TOTAL')
                );

                $total_data = count($list_sales);
                $total_sales = 0;
                for ($i=0; $i < $total_data; $i++) {
                    array_push($header, [$i + 1,
                        $list_sales[$i]['formulir']->form_number,
                        $list_sales[$i]['formulir']->form_date,
                        $list_sales[$i]['customer']->codeName,
                        $list_sales[$i]['formulir']->createdBy->name,
                        number_format_quantity($list_sales[$i]['total'], 0)
                    ]);

                    $total_sales += $list_sales[$i]['total'];
                }

                $total_data = $total_data+3;
                $sheet->fromArray($header, null, 'A3', false, false);
                $sheet->setBorder('A3:F'.$total_data, 'thin');

                // Set Total Sales
                $next_row = $total_data + 1;
                $sheet->cell('E'.$next_row, function ($cell) {
                    $cell->setValue('TOTAL');
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '12',
                        'bold'       =>  true
                    ));
                });

                $sheet->cell('F'.$next_row, function ($cell) use ($total_data, $total_sales) {
                    $cell->setValue(number_format_quantity($total_sales, 0));
                });
            });
        })->export('xls');
    }

    public function generateReport($list_sales, $date_from, $date_to, $search)
    {
        \Excel::create('Sales Report', function ($excel) use ($list_sales, $date_from, $date_to, $search) {
            # Sheet Data
            $excel->sheet('Data', function ($sheet) use ($list_sales, $date_from, $date_to, $search) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                    'C' => 25,
                    'D' => 25,
                    'E' => 25,
                    'F' => 25,
                    'G' => 25,
                ));

                $title = "SALES DATA FROM " . $date_from . " - " . $date_to . " WITH KEYWORD '" . $search . "'";
                $info_export = "DATE EXPORT ". \Carbon::now();
                $sheet->cell('A1', function ($cell) use ($title) {
                    $cell->setValue($title);
                });
                $sheet->cell('A2', function ($cell) use ($info_export) {
                    $cell->setValue($info_export);
                });

                // MERGER COLUMN
                $sheet->mergeCells('A4:F4', 'center');
                $sheet->cell('A4', function ($cell) {
                    // Set font
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '14',
                        'bold'       =>  true
                    ));

                    $cell->setValue('SALES REPORT');
                });

                $sheet->mergeCells('A5:F5', 'center');
                $sheet->cell('A5', function ($cell) use ($date_from, $date_to) {
                    // Set font
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '14',
                        'bold'       =>  true
                    ));
                    
                    if ($date_from && $date_to) {
                        $cell->setValue('PERIOD : '. strtoupper(DateHelper::formatView(date_format_db($date_from)) . ' TO ' . DateHelper::formatView(date_format_db($date_to))));
                    } else {
                        $cell->setValue('PERIOD : '. strtoupper(DateHelper::formatView(\Carbon::now())));
                    }
                });

                $sheet->cell('A6:F6', function ($cell) {
                    // Set font
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '12',
                        'bold'       =>  true
                    ));
                });

                // Generad table of content
                $header = array(
                    array('NO', 'FORM NUMBER', 'FORM DATE', 'CUSTOMER', 'SALES', 'TOTAL')
                );

                $total_data = count($list_sales);
                $total_sales = 0;
                for ($i=0; $i < $total_data; $i++) {
                    array_push($header, [$i + 1,
                        $list_sales[$i]['formulir']->form_number,
                        $list_sales[$i]['formulir']->form_date,
                        $list_sales[$i]['customer']->codeName,
                        $list_sales[$i]['formulir']->createdBy->name,
                        number_format_quantity($list_sales[$i]['total'], 0)
                    ]);

                    $total_sales += $list_sales[$i]['total'];
                }

                $total_data = $total_data+6;
                $sheet->fromArray($header, null, 'A6', false, false);
                $sheet->setBorder('A6:F'.$total_data, 'thin');

                // Set Total Sales
                $next_row = $total_data + 1;
                $sheet->cell('E'.$next_row, function ($cell) {
                    $cell->setValue('TOTAL');
                    $cell->setFont(array(
                        'family'     => 'Times New Roman',
                        'size'       => '12',
                        'bold'       =>  true
                    ));
                });

                $sheet->cell('F'.$next_row, function ($cell) use ($total_data, $total_sales) {
                    $cell->setValue(number_format_quantity($total_sales, 0));
                });
            });
        })->export('xls');
    }
}

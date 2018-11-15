<?php

namespace Point\PointFinance\Http\Controllers\DebtCash;

use Illuminate\Routing\Controller;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Person;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\Cash\Cash;
use Point\PointFinance\Models\Cash\CashCashAdvance;
use Point\PointFinance\Models\CashAdvance;

class ReportController extends Controller
{
    use ValidationTrait;

    public function index($type)
    {
        self::checkingPermission($type);
        $view = view('point-finance::app.finance.point.debt-cash-report.report');
        if ($type == 'cash') {
            $view->list_coa = Coa::where('coa_category_id', 8)->active()->get();
        } elseif ($type == 'bank') {
            $view->list_coa = Coa::where('coa_category_id', 2)->active()->get();
        }

        $view->list_person = Person::active()->get();
        $view->type = $type;

        return $view;
    }

    public function checkingPermission($type)
    {
        if ($type == 'bank') {
            access_is_allowed('read.point.finance.bank.report');
        } elseif ($type == 'cash') {
            access_is_allowed('read.point.finance.cash.report');
        } else {
            abort(403);
        }
    }

    public function _view()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $type = \Input::get('type');
        $subledger = \Input::get('subledger_id');
        $coa_id = \Input::get('coa_id');
        $date_from = \Input::get('date_from') ? \Input::get('date_from') : date('d-m-Y');
        $date_to = \Input::get('date_to') ? \Input::get('date_to') : date('d-m-Y');

        // respon view
        $report = self::dataReport($type, $date_from, $date_to, $coa_id, $subledger);
        $view = view('point-finance::app.finance.point.debt-cash-report._detail');
        $view->list_report = $report['report'];
        $view->type = $type;
        $view->total_cash_advance = CashAdvance::joinFormulir()->selectOriginal()->notArchived()->notCanceled()
            ->where('formulir.form_date', '<=', date_format_db($date_to, 'end'))
            ->where('is_payed', true)
            ->where('amount', '>', 0)
            ->where('coa_id', $coa_id)
            ->handedOver()
            ->sum('amount');

        $view->total_cash_advance_used = CashCashAdvance::joinFormulir()->selectOriginal()->notArchived()->notCanceled()
            ->where('formulir.form_date', '<=', date_format_db($date_to, 'end'))
            ->where('cash_advance_amount', '>', 0)
            ->sum('cash_advance_amount');

        $view->total_cash_advance_remaining = CashAdvance::joinFormulir()->selectOriginal()->notArchived()->notCanceled()
            ->where('formulir.form_date', '<=', date_format_db($date_to, 'end'))
            ->where('is_payed', true)
            ->where('amount', '>', 0)
            ->where('coa_id', $coa_id)
            ->handedOver()
            ->sum('remaining_amount');

        // \Log::info('amount ' . $view->total_cash_advance);
        // \Log::info('used ' . $view->total_cash_advance_used);


        $view->opening_balance = $report['journal_debit'] - $report['journal_credit'];
        $view->url = url('finance/point/debt-report/export/?type=' . $type . '&subledger_id=' . $subledger . '&coa_id=' . $coa_id . '&date_from=' . $date_from . '&date_to=' . $date_to);
        $view->url_pdf = url('finance/point/debt-report/export/pdf?type=' . $type . '&subledger_id=' . $subledger . '&coa_id=' . $coa_id . '&date_from=' . $date_from . '&date_to=' . $date_to);

        $view->list_user = User::active()->get();

        return $view;
    }

    public function exportPDF()
    {
        $type = \Input::get('type');
        $subledger = \Input::get('subledger_id');
        $coa_id = \Input::get('coa_id');
        $date_from = \Input::get('date_from') ? \Input::get('date_from') : date('d-m-Y');
        $date_to = \Input::get('date_to') ? \Input::get('date_to') : date('d-m-Y');

        // respon view
        $report = self::dataReport($type, $date_from, $date_to, $coa_id, $subledger);
        $opening_balance = $report['journal_debit'] - $report['journal_credit'];
        $type = $type;
        $list_report = $report['report'];
        $pdf = \PDF::loadView('point-finance::app.finance.point.debt-cash-report.report-pdf', ['list_report' => $list_report, 'opening_balance' => $opening_balance, 'type' => $type]);

        return $pdf->stream();
    }

    public static function dataReport($type, $date_from, $date_to, $coa_id, $subledger)
    {
        // payment type cash
        $report_type = Cash::joinFormulir()->where('coa_id', $coa_id)->notArchived()->close()->selectOriginal()->orderByStandardAsc();

        // payment type bank
        if ($type == 'bank') {
            $report_type = Bank::joinFormulir()->where('coa_id', $coa_id)->notArchived()->close()->selectOriginal()->orderByStandardAsc();
        }

        // getting data from Journal
        $journal_debit = Journal::where('form_date', '<', \DateHelper::formatDB($date_from))
            ->where('coa_id', $coa_id)
            ->sum('debit');
        $journal_credit = Journal::where('form_date', '<', \DateHelper::formatDB($date_from))
            ->where('coa_id', $coa_id)
            ->sum('credit');

        // filter subledger
        $report = $report_type
            ->whereBetween('form_date', array(\DateHelper::formatDB($date_from), \DateHelper::formatDB($date_to, 'end')))
            ->get();

        if ($subledger) {
            $report = $report_type
                ->whereBetween('form_date', array(\DateHelper::formatDB($date_from), \DateHelper::formatDB($date_to, 'end')))
                ->where('person_id', $subledger)
                ->get();
        }

        return [
            'report' => $report,
            'journal_debit' => $journal_debit,
            'journal_credit' => $journal_credit
        ];
    }

    public function export()
    {
        $type = \Input::get('type');
        $coa_id = \Input::get('coa_id');
        $subledger_id = \Input::get('subledger_id');
        $date_from = \Input::get('date_from');
        $date_to = \Input::get('date_to');

        \Excel::create($type . ' Report', function ($excel) use ($type, $coa_id, $subledger_id, $date_from, $date_to) {
            # Sheet Data
            $excel->sheet('Data', function ($sheet) use ($type, $coa_id, $subledger_id, $date_from, $date_to) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                    'C' => 25,
                    'D' => 25,
                    'E' => 25,
                    'F' => 25,
                    'G' => 25,
                    'H' => 25,
                    'I' => 25,
                ));

                $sheet->setColumnFormat(array(
                    'H' => '#,##0.00',
                    'I' => '#,##0.00'
                ));

                $title = strtoupper($type . " REPORT FROM " . $date_from . " - " . $date_to);
                $info_export = "DATE EXPORT " . \Carbon::now();
                $sheet->cell('A1', function ($cell) use ($title) {
                    $cell->setValue($title);
                });
                $sheet->cell('A2', function ($cell) use ($info_export) {
                    $cell->setValue($info_export);
                });

                // MERGER COLUMN
                $sheet->mergeCells('A4:I4', 'center');
                $sheet->cell('A4', function ($cell) use ($type) {
                    // Set font
                    $cell->setFont(array(
                        'family' => 'Times New Roman',
                        'size' => '14',
                        'bold' => true
                    ));

                    $cell->setValue(strtoupper($type . ' REPORT'));
                });

                $sheet->mergeCells('A5:I5', 'center');
                $sheet->cell('A5', function ($cell) use ($date_from, $date_to) {
                    // Set font
                    $cell->setFont(array(
                        'family' => 'Times New Roman',
                        'size' => '14',
                        'bold' => true
                    ));

                    if ($date_from && $date_to) {
                        $cell->setValue('PERIOD : ' . strtoupper(\DateHelper::formatView(date_format_db($date_from)) . ' TO ' . \DateHelper::formatView(date_format_db($date_to))));
                    } else {
                        $cell->setValue('PERIOD : ' . strtoupper(\DateHelper::formatView(\Carbon::now())));
                    }
                });

                $sheet->cell('A6:I6', function ($cell) {
                    // Set font
                    $cell->setFont(array(
                        'family' => 'Times New Roman',
                        'size' => '12',
                        'bold' => true
                    ));
                });

                // Generad table of content
                $header = array(
                    array('NO', 'FORM DATE', 'FORM NUMBER', 'PERSON', 'ACCOUNT', 'ACCOUNT DESCRIPTION', 'NOTES', 'RECEIVED', 'DISBURSED')
                );

                $data_report = self::dataReport($type, $date_from, $date_to, $coa_id, $subledger_id);

                $total_data = count($data_report['report']);
                $total_row = 0;
                $total_received = 0;
                $total_disbursed = 0;
                // $received = 0;
                for ($i = 0; $i < $total_data; $i++) {
                    foreach ($data_report['report'][$i]->detail as $report_detail) {
                        $total_row++;
                        $received = '0.00';
                        if ($data_report['report'][$i]->payment_flow == 'in') {
                            $received = $report_detail->amount;
                            $total_received += $report_detail->amount;
                        }

                        $disbursed = '0.00';
                        if ($data_report['report'][$i]->payment_flow == 'out') {
                            $disbursed = $report_detail->amount;
                            $total_disbursed += $report_detail->amount;
                        }

                        $coa = Coa::find($report_detail->coa_id);

                        array_push($header, [
                            $total_row,
                            date_format_view($data_report['report'][$i]->formulir->form_date),
                            $data_report['report'][$i]->formulir->form_number,
                            $data_report['report'][$i]->person->codeName,
                            $coa->coa_number,
                            $coa->name,
                            $report_detail->notes_detail,
                            $received * 1,
                            $disbursed * 1
                        ]);
                    }
                }

                $total_row = $total_row + 6;
                $sheet->fromArray($header, null, 'A6', false, false);
                $sheet->setBorder('A6:I' . $total_row, 'thin');

                $next_row = $total_row + 1;
                $sheet->cell('G' . $next_row, function ($cell) {
                    $cell->setValue('TOTAL');
                    $cell->setFont(array(
                        'family' => 'Times New Roman',
                        'size' => '12',
                        'bold' => true
                    ));
                });

                $sheet->cell('H' . $next_row, function ($cell) use ($total_received) {
                    $cell->setValue($total_received);
                });

                $sheet->cell('I' . $next_row, function ($cell) use ($total_disbursed) {
                    $cell->setValue($total_disbursed);
                });

                $next_row = $next_row + 1;
                $sheet->cell('G' . $next_row, function ($cell) {
                    $cell->setValue('OPENING BALANCE');
                    $cell->setFont(array(
                        'family' => 'Times New Roman',
                        'size' => '12',
                        'bold' => true
                    ));
                });

                $sheet->cell('H' . $next_row, function ($cell) use ($data_report) {
                    $cell->setValue($data_report['journal_debit'] - $data_report['journal_credit']);
                });

                $next_row = $next_row + 1;
                $sheet->cell('G' . $next_row, function ($cell) {
                    $cell->setValue('ENDING BALANCE');
                    $cell->setFont(array(
                        'family' => 'Times New Roman',
                        'size' => '12',
                        'bold' => true
                    ));
                });

                $sheet->cell('H' . $next_row, function ($cell) use ($data_report, $total_received, $total_disbursed) {
                    $cell->setValue(($data_report['journal_debit'] - $data_report['journal_credit']) + $total_received - $total_disbursed);
                });
            });
        })->export('xls');
    }

    public function sendRequestApproval($type)
    {
        self::checkingPermission($type);

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $data = [];
        $approver = \Input::get('approver');
        $data['approver'] = User::find($approver);

        $subledger = \Input::get('subledger_id');
        $coa_id = \Input::get('coa_id');
        $date_from = \Input::get('date_from') ? \Input::get('date_from') : date('d-m-Y');
        $date_to = \Input::get('date_to') ? \Input::get('date_to') : date('d-m-Y');

        $report = self::dataReport($type, $date_from, $date_to, $coa_id, $subledger);
        $data['list_report'] = $report['report'];
        $data['type'] = $type;
        $data['total_cash_advance'] = CashAdvance::joinFormulir()->selectOriginal()->notArchived()->notCanceled()
            ->where('formulir.form_date', '<=', date_format_db($date_to, 'end'))
            ->where('is_payed', true)
            ->where('amount', '>', 0)
            ->where('coa_id', $coa_id)
            ->handedOver()
            ->sum('amount');

        $data['total_cash_advance_used'] = CashCashAdvance::joinFormulir()->selectOriginal()->notArchived()->notCanceled()
            ->where('formulir.form_date', '<=', date_format_db($date_to, 'end'))
            ->where('cash_advance_amount', '>', 0)
            ->sum('cash_advance_amount');

        $data['total_cash_advance_remaining'] = CashAdvance::joinFormulir()->selectOriginal()->notArchived()->notCanceled()
            ->where('formulir.form_date', '<=', date_format_db($date_to, 'end'))
            ->where('is_payed', true)
            ->where('amount', '>', 0)
            ->where('coa_id', $coa_id)
            ->handedOver()
            ->sum('remaining_amount');

        $data['opening_balance'] = $report['journal_debit'] - $report['journal_credit'];

        self::sendingRequestApproval($data, auth()->user()->name, url('/'));
    }

    public static function sendingRequestApproval($data, $requester, $domain)
    {
        $data['token'] = md5(date('ymdhis'));
        $data['requester'] = $requester;
        $data['url'] = $domain;

        sendEmail('point-finance::emails.finance.point.approval.cash-mirna', $data, $data['approver']->email, 'Debt Cash Report #' . date('ymdHi'));

        // foreach ($list_downpayment as $downpayment) {
        //     formulir_update_token($downpayment->formulir, $token);
        // }
    }

    // public function approve(Request $request, $id)
    // {
    //     $downpayment = Downpayment::find($id);
    //     $approval_message = \Input::get('approval_message') ? : '';
    //     $token = \Input::get('token');

    //     DB::beginTransaction();

    //     FormulirHelper::approve($downpayment->formulir, $approval_message, 'approval.point.sales.service.downpayment', $token);
    //     self::addPaymentReference($downpayment);
    //     timeline_publish('approve', 'downpayment ' . $downpayment->formulir->form_number . ' approved', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));
    //     DB::commit();

    //     gritter_success('form approved', 'false');
    //     return $this->getRedirectLink($request, $downpayment->formulir);
    // }

    // public function reject(Request $request, $id)
    // {
    //     $downpayment = Downpayment::find($id);
    //     $approval_message = \Input::get('approval_message') ? : '';
    //     $token = \Input::get('token');

    //     DB::beginTransaction();

    //     FormulirHelper::reject($downpayment->formulir, $approval_message, 'approval.point.sales.service.downpayment', $token);
    //     timeline_publish('reject', 'downpayment ' . $downpayment->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));

    //     DB::commit();

    //     gritter_success('form rejected', 'false');
    //     return $this->getRedirectLink($request, $downpayment->formulir);
    // }

    // public function approveAll()
    // {
    //     $token = \Input::get('token');
    //     $array_formulir_id = explode(',', \Input::get('formulir_id'));
    //     $approval_message = '';

    //     DB::beginTransaction();
    //     foreach ($array_formulir_id as $id) {
    //         $downpayment = Downpayment::where('formulir_id', $id)->first();
    //         FormulirHelper::approve($downpayment->formulir, $approval_message, 'approval.point.sales.service.downpayment', $token);
    //         self::addPaymentReference($downpayment);
    //         timeline_publish('approve', $downpayment->formulir->form_number . ' approved', $downpayment->formulir->approval_to);
    //     }
    //     DB::commit();

    //     $view = view('framework::app.approval-all-status');
    //     $view->array_formulir_id = $array_formulir_id;
    //     $view->formulir = \Input::get('formulir_id');

    //     return $view;
    // }

    // public function rejectAll()
    // {
    //     $token = \Input::get('token');
    //     $array_formulir_id = explode(',', \Input::get('formulir_id'));
    //     $approval_message = '';

    //     DB::beginTransaction();
    //     foreach ($array_formulir_id as $id) {
    //         $downpayment = Downpayment::where('formulir_id', $id)->first();
    //         FormulirHelper::reject($downpayment->formulir, $approval_message, 'approval.point.sales.service.downpayment', $token);
    //         timeline_publish('reject', $downpayment->formulir->form_number . ' rejected', $downpayment->formulir->approval_to);
    //     }
    //     DB::commit();

    //     $view = view('framework::app.approval-all-status');
    //     $view->array_formulir_id = $array_formulir_id;
    //     $view->formulir = \Input::get('formulir_id');

    //     return $view;
    // }
}

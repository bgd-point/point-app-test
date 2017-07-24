<?php

namespace Point\BumiDeposit\Http\Controllers;

use Point\Framework\Http\Controllers\Controller;
use Point\BumiDeposit\Models\Deposit;
use Point\BumiDeposit\Models\DepositGroup;
use Point\BumiDeposit\Models\DepositOwner;
use Point\BumiDeposit\Models\Bank;

class DepositReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.bumi.deposit.report');

        $selected_group = \Input::get('deposit_group_id');
        if ($selected_group > 0) {
            $selected_group = DepositGroup::where('id', '=', $selected_group)->get();
        } else {
            $selected_group = DepositGroup::active()->get();
        }

        return view('bumi-deposit::app.facility.bumi-deposit.deposit-report.index', array(
            'deposits' => Deposit::joinFormulir()->notArchived()->selectOriginal()->orderByStandard()->get(),
            'owners' => DepositOwner::active()->get(),
            'groups' => DepositGroup::active()->get(),
            'selected_group' => $selected_group,
            'banks' => Bank::active()->get()
        ));
    }

    public function excel()
    {
        access_is_allowed('export.bumi.deposit.report');

        \Excel::create('Deposit Report ' . date('d F Y'), function ($excel) {
            $excel->sheet('Deposit Report', function ($sheet) {
                $selected_group = \Input::get('deposit_group_id');
                if ($selected_group > 0) {
                    $selected_group = DepositGroup::where('id', '=', $selected_group)->get();
                } else {
                    $selected_group = DepositGroup::active()->get();
                }

                $sheet->loadView('bumi-deposit::app.facility.bumi-deposit.deposit-report.excel',
                    array(
                        'deposits' => Deposit::joinFormulir()->notArchived()->selectOriginal()->orderByStandard()->get(),
                        'owners' => DepositOwner::active()->get(),
                        'groups' => DepositGroup::active()->get(),
                        'selected_group' => $selected_group,
                        'banks' => Bank::active()->get()
                    )
                );
                $sheet->protect('point');
            });
        })->export('xls');

        return redirect()->back();
    }
}

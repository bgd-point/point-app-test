<?php

namespace Point\Ksp\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoanSimulatorController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        access_is_allowed('read.bumi.shares.buy');

        $view = view('ksp::app.facility.ksp.loan-simulator.index');

        $view->loan_amount = 0;
        $view->periods = 12;
        $view->interest = 0;
        $view->interest_rate_type = 'flat';

        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'periods' => 'required|min:1|max:600',
            'loan_amount' => 'required|min:1',
            'interest' => 'required',
        ]);

        $view = view('ksp::app.facility.ksp.loan-simulator.index');

        $view->loan_amount = number_format_db($request->get('loan_amount'));
        $view->periods = number_format_db($request->get('periods'));
        $view->interest = number_format_db($request->get('interest'));
        $view->interest_rate_type = $request->get('interest_rate_type');
        return $view;
    }
}

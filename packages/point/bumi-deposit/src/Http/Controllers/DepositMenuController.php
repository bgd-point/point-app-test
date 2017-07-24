<?php

namespace Point\BumiDeposit\Http\Controllers;

use Point\Framework\Http\Controllers\Controller;

class DepositMenuController extends Controller
{
    public function index()
    {
        access_is_allowed('menu.bumi.deposit');

        return view('bumi-deposit::app.facility.bumi-deposit.menu');
    }
}

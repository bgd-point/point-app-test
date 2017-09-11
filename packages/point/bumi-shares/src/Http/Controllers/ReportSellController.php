<?php

namespace Point\BumiShares\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\BumiShares\Models\Sell;
use Point\BumiShares\Models\StockFifo;

class ReportSellController extends Controller
{
	public function index()
    {
        access_is_allowed('read.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.sell.index');
        $view->list_stock_fifo = StockFifo::joinFormulirSell()->select('bumi_shares_stock_fifo.*')->paginate(100);

        return $view;
    }
}
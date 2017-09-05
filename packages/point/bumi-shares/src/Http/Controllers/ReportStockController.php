<?php

namespace Point\BumiShares\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Point\BumiShares\Models\Buy;
use Point\BumiShares\Models\OwnerGroup;
use Point\BumiShares\Models\SellingPrice;
use Point\BumiShares\Models\Shares;
use Point\BumiShares\Models\Stock;
use Point\BumiShares\Models\StockFifo;

class ReportStockController extends Controller
{
    public function index()
    {
        access_is_allowed('read.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.index');
        $view->list_owner_group = OwnerGroup::active()->get();
        $view->list_shares = Shares::active()->get();

        $view->group = app('request')->input('group_id') ? OwnerGroup::find(app('request')->input('group_id')) : '';
        $view->shares = app('request')->input('shares_id') ? Shares::find(app('request')->input('shares_id')) : '';
        $view->list_stock_shares = Stock::where('remaining_quantity', '>', 0);

        if (app('request')->input('group_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('owner_group_id', '=', app('request')->input('group_id'));
        }

        if (app('request')->input('shares_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('shares_id', '=', app('request')->input('shares_id'));
        }

        $view->list_stock_shares = $view->list_stock_shares->orderBy('shares_id');

        $view->total_quantity = 0;
        $view->total_value = 0;
        $view->total_selling = 0;
        $view->estimation_of_selling_value = 0;
        $view->estimation_of_profit_and_loss = 0;
        return $view;
    }

    public function detail($formulir_id, $shares_id)
    {
        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.detail');
        $view->list_stock_fifo = StockFifo::joinFormulirSell()->where('shares_in_id', $formulir_id)->where('quantity', '>', 0)->get();
        $view->buy = Buy::where('formulir_id', $formulir_id)->first();
        $view->shares = Shares::find($shares_id);
        return $view;
    }

    public function excel()
    {
        access_is_allowed('export.bumi.shares.report');

        \Excel::create('Shares Stock Report '.date('d F Y'), function ($excel) {
            $excel->sheet('Stock', function ($sheet) {
                $list_owner_group = OwnerGroup::active()->get();
                $list_shares = Shares::active()->get();

                $group = app('request')->input('group_id') ? OwnerGroup::find(app('request')->input('group_id')) : '';
                $shares = app('request')->input('shares_id') ? Shares::find(app('request')->input('shares_id')) : '';
                $list_stock_shares = Stock::where('remaining_quantity', '>', 0);

                if (app('request')->input('group_id')) {
                    $list_stock_shares = $list_stock_shares->where('owner_group_id', '=', app('request')->input('group_id'));
                }

                if (app('request')->input('shares_id')) {
                    $list_stock_shares = $list_stock_shares->where('shares_id', '=', app('request')->input('shares_id'));
                }

                $list_stock_shares = $list_stock_shares->orderBy('shares_id');

                $total_quantity = 0;
                $total_value = 0;
                $total_selling = 0;
                $estimation_of_selling_value = 0;
                $estimation_of_profit_and_loss = 0;

                $data = array(
                    'list_owner_group' => $list_owner_group,
                    'list_stock_shares' => $list_shares,
                    'group' => $group,
                    'shares' => $shares,
                    'list_stock_shares' => $list_stock_shares,
                    'total_quantity' => $total_quantity,
                    'total_value' => $total_value,
                    'total_selling' => $total_selling,
                    'estimation_of_selling_value' => $estimation_of_selling_value,
                    'estimation_of_profit_and_loss' => $estimation_of_profit_and_loss,
                 );
                $sheet->loadView('bumi-shares::app.facility.bumi-shares.report.stock.excel', $data);
                $sheet->setColumnFormat(array(
                    'A:O' => '0.00'
                ));
                $sheet->protect('password');
            });
        })->export('xls');

        return redirect()->back();
    }

    public function printReport()
    {
        access_is_allowed('export.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.print');
        $view->list_owner_group = OwnerGroup::active()->get();
        $view->list_shares = Shares::active()->get();

        $view->group = app('request')->input('group_id') ? OwnerGroup::find(app('request')->input('group_id')) : '';
        $view->shares = app('request')->input('shares_id') ? Shares::find(app('request')->input('shares_id')) : '';
        $view->list_stock_shares = Stock::where('remaining_quantity', '>', 0);

        if (app('request')->input('group_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('owner_group_id', '=', app('request')->input('group_id'));
        }

        if (app('request')->input('shares_id')) {
            $view->list_stock_shares = $view->list_stock_shares->where('shares_id', '=', app('request')->input('shares_id'));
        }

        $view->list_stock_shares = $view->list_stock_shares->orderBy('shares_id');

        $view->total_quantity = 0;
        $view->total_value = 0;
        $view->total_selling = 0;
        $view->estimation_of_selling_value = 0;
        $view->estimation_of_profit_and_loss = 0;
        return $view;
    }

    public function estimateOfSellingPrice()
    {
        access_is_allowed('read.bumi.shares.report');

        $view = view('bumi-shares::app.facility.bumi-shares.report.stock.estimate-of-selling-price');
        $view->list_stock_shares = Shares::active()->get();
        return $view;
    }

    public function updateEstimateOfSellingPrice()
    {
        access_is_allowed('read.bumi.shares.report');

        DB::beginTransaction();

        $shares_id = app('request')->input('shares_id');
        $price = app('request')->input('price');

        for ($i=0;$i<count(app('request')->input('price'));$i++) {
            $old_estimation = SellingPrice::where('shares_id', '=', $shares_id[$i])->first();

            if ($old_estimation) {
                $old_estimation->price = \NumberHelper::formatDB($price[$i]);
                $old_estimation->save();
            } else {
                $estimation = new SellingPrice;
                $estimation->price = \NumberHelper::formatDB($price[$i]);
                $estimation->shares_id = $shares_id[$i];
                $estimation->updated_by = \Auth::user()->id;
                $estimation->save();
            }
        }

        DB::commit();

        gritter_success('update selling price success');
        return redirect('facility/bumi-shares/report/stock');
    }

    public function detailExport($formulir_id, $shares_id)
    {
        $file_name = 'Shares Mutation '.auth()->user()->id . '' . date('Y-m-d_His');
        \Excel::create($file_name, function($excel) use ($formulir_id, $shares_id) {
            $excel->sheet('Shares Stock Report', function($sheet) use ($formulir_id, $shares_id) {
                $data = array(
                    'list_stock_fifo' => StockFifo::joinFormulirSell()->where('shares_in_id', $formulir_id)->where('quantity', '>', 0)->get(),
                    'buy' => Buy::where('formulir_id', $formulir_id)->first(),
                    'shares' => Shares::find($shares_id)
                 );
                
                $sheet->loadView('bumi-shares::app.facility.bumi-shares.report.stock._data-detail', $data);
            });

        })->export('xls');
    }
}

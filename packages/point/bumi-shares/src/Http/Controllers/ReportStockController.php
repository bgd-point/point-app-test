<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Point\BumiShares\Models\Stock;
use Point\BumiShares\Models\OwnerGroup;
use Point\BumiShares\Models\Shares;
use Point\BumiShares\Models\SellingPrice;
use App\Http\Controllers\Controller;

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

                $sheet->loadView('bumi-shares::app.facility.bumi-shares.report.stock.excel')
                    ->with('list_owner_group', $list_owner_group)
                    ->with('list_stock_shares', $list_shares)
                    ->with('group', $group)
                    ->with('shares', $shares)
                    ->with('list_stock_shares', $list_stock_shares)
                    ->with('total_quantity', $total_quantity)
                    ->with('total_value', $total_value)
                    ->with('total_selling', $total_selling)
                    ->with('estimation_of_selling_value', $estimation_of_selling_value)
                    ->with('estimation_of_profit_and_loss', $estimation_of_profit_and_loss);
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
            $old_perkiraan = SellingPrice::where('shares_id', '=', $shares_id[$i])->first();

            if ($old_perkiraan) {
                $old_perkiraan->price = \NumberHelper::formatDB($price[$i]);
                $old_perkiraan->save();
            } else {
                $perkiraan = new SellingPrice;
                $perkiraan->price = \NumberHelper::formatDB($price[$i]);
                $perkiraan->shares_id = $shares_id[$i];
                $perkiraan->updated_by = \Auth::user()->id;
                $perkiraan->save();
            }
        }

        DB::commit();

        gritter_success('update selling price success');
        return redirect('facility/bumi-shares/report/stock');
    }
}

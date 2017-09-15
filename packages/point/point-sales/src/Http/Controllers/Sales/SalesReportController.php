<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\PointSales\Helpers\SalesReportHelper;

class SalesReportController extends Controller
{
	public function index()
	{
		access_is_allowed('read.point.sales.report');

        $view = view('point-sales::app.sales.point.sales.report.index');
        $view->list_report = SalesReportHelper::searchList(\Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->paginate(100);

        return $view;
	}
}
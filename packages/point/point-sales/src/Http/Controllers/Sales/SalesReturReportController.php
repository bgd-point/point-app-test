<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\EmailHistory;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Helpers\InvoiceHelper;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\InvoiceItem;
use Point\PointSales\Models\Sales\Retur;
use Point\PointSales\Models\Sales\ReturItem;

class SalesReturReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.invoice');

        $view = view('point-sales::app.sales.point.sales.retur-report.index');
        $view->listRetur = Retur::joinFormulir()
            ->where('formulir.form_status', '>=', 0)
            ->notArchived()
            ->approvalApproved()
            ->select('point_sales_retur.*')
            ->orderBy('formulir.form_date', 'desc')
            ->paginate(100);
        return $view;
    }
}

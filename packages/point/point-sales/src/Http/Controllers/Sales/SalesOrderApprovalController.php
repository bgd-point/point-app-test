<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointSales\Helpers\SalesOrderHelper;
use Point\PointSales\Models\Sales\SalesOrder;

class SalesOrderApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.sales.order');
        
        $view = view('point-sales::app.sales.point.sales.sales-order.request-approval');
        $view->list_sales_order = SalesOrder::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.sales.order');
        
        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_sales_order_id, $requester, $domain=url('/'))
    {
        $list_approver = SalesOrder::selectApproverList($list_sales_order_id);
        $token = md5(date('ymdhis'));
        
        foreach ($list_approver as $data_approver) {
            $list_sales_order = SalesOrder::selectApproverRequest($list_sales_order_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_sales_order as $sales_order) {
                array_push($array_formulir_id, $sales_order->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_sales_order,
                'token' => $token,
                'requester' => $requester,
                'url' => $domain,
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            sendEmail(SalesOrder::bladeEmail(), $data, $approver->email, 'Request Approval Sales Order #' . date('ymdHi'));

            foreach ($list_sales_order as $sales_order) {
                formulir_update_token($sales_order->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $sales_order = SalesOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');
        
        DB::beginTransaction();
        
        FormulirHelper::approve($sales_order->formulir, $approval_message, 'approval.point.sales.order', $token);

        // add to expedition order reference
        if (! $sales_order->include_expedition) {
            SalesOrderHelper::registerToExpedition($sales_order);
        }

        timeline_publish('approve', 'sales order ' . $sales_order->formulir->form_number . ' approved', $this->getUserForTimeline($request, $sales_order->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $sales_order->formulir);
    }

    public function reject(Request $request, $id)
    {
        $sales_order = SalesOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');
        
        DB::beginTransaction();

        FormulirHelper::reject($sales_order->formulir, $approval_message, 'approval.point.sales.order', $token);
        timeline_publish('reject', 'sales quotation ' . $sales_order->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $sales_order->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $sales_order->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $sales_order = SalesOrder::where('formulir_id', $id)->first();
            FormulirHelper::approve($sales_order->formulir, $approval_message, 'approval.point.sales.order', $token);
            if (! $sales_order->include_expedition) {
                SalesOrderHelper::registerToExpedition($sales_order);
            }

            timeline_publish('approve', $sales_order->formulir->form_number . ' approved', $sales_order->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }

    public function rejectAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $sales_order = SalesOrder::where('formulir_id', $id)->first();
            FormulirHelper::reject($sales_order->formulir, $approval_message, 'approval.point.sales.order', $token);
            timeline_publish('reject', $sales_order->formulir->form_number . ' rejected', $sales_order->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

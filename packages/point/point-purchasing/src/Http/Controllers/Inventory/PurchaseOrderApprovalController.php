<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointPurchasing\Helpers\PurchaseOrderHelper;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;

class PurchaseOrderApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.order');
        
        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-order.request-approval');
        $view->list_purchase_order = PurchaseOrder::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.order');
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_purchase_order_id, $requester, $domain=url('/'))
    {
        $list_approver = PurchaseOrder::selectApproverList($list_purchase_order_id);
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_purchase_order = PurchaseOrder::selectApproverRequest($list_purchase_order_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_purchase_order as $purchase_order) {
                array_push($array_formulir_id, $purchase_order->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_purchase_order,
                'token' => $token,
                'requester' => $requester,
                'url' => $domain,
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            sendEmail(PurchaseOrder::bladeEmail(), $data, $approver->email, 'Request Approval Purchase Order #' . date('ymdHi'));

            foreach ($list_purchase_order as $purchase_order) {
                formulir_update_token($purchase_order->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $purchase_order = PurchaseOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();
        
        FormulirHelper::approve($purchase_order->formulir, $approval_message, 'approval.point.purchasing.order', $token);
        PurchaseOrderHelper::registerToExpedition($purchase_order);
        timeline_publish('approve', $purchase_order->formulir->form_number . ' approved', $this->getUserForTimeline($request, $purchase_order->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $purchase_order->formulir);
    }

    public function reject(Request $request, $id)
    {
        $purchase_order = PurchaseOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($purchase_order->formulir, $approval_message, 'approval.point.purchasing.order', $token);
        timeline_publish('reject', 'purchase order ' . $purchase_order->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $purchase_order->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $purchase_order->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $purchase_order = PurchaseOrder::where('formulir_id', $id)->first();
            FormulirHelper::approve($purchase_order->formulir, $approval_message, 'approval.point.purchasing.order', $token);
            PurchaseOrderHelper::registerToExpedition($purchase_order);
            timeline_publish('approve', $purchase_order->formulir->form_number . ' approved', $purchase_order->formulir->approval_to);
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
            $purchase_order = PurchaseOrder::where('formulir_id', $id)->first();
            FormulirHelper::reject($purchase_order->formulir, $approval_message, 'approval.point.purchasing.order', $token);
            timeline_publish('reject', $purchase_order->formulir->form_number . ' rejected', $purchase_order->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

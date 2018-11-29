<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointPurchasing\Models\Service\PurchaseOrder;

class PurchaseOrderApprovalController extends Controller {
    use ValidationTrait, RequestApprovalTrait;

    /**
     * @return mixed
     */
    public function requestApproval() {
        access_is_allowed('create.point.purchasing.service.purchase.order');

        $view            = view('point-purchasing::app.purchasing.point.service.purchase-order.request-approval');
        $purchase_orders = PurchaseOrder::selectRequestApproval()->paginate(100);

        $view->list_purchase_order = $purchase_orders;

        return $view;
    }

    /**
     * @param Request $request
     */
    public function sendRequestApproval(Request $request) {
        access_is_allowed('create.point.purchasing.service.purchase.order');
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name, url('/'));

        gritter_success('You have sent email for purchase order approval');

        return redirect()->back();
    }

    /**
     * @param $list_purchase_order_id
     * @param $requester
     * @param $domain
     */
    public static function sendingRequestApproval($list_purchase_order_id, $requester, $domain) {
        $token         = md5(date('ymdhis'));
        $list_approver = PurchaseOrder::selectApproverList($list_purchase_order_id);

        foreach ($list_approver as $data_approver) {
            $list_purchase_order = PurchaseOrder::selectApproverRequest($list_purchase_order_id, $data_approver->approval_to);
            $array_formulir_id   = [];
            foreach ($list_purchase_order as $purchase_order) {
                array_push($array_formulir_id, $purchase_order->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver          = User::find($data_approver->approval_to);
            $data              = [
                'list_purchase_order' => $list_purchase_order,
                'token'               => $token,
                'requester'           => $requester,
                'url'                 => $domain,
                'approver'            => $approver,
                'array_formulir_id'   => $array_formulir_id,

            ];
            sendEmail(PurchaseOrder::bladeEmail(), $data, $approver->email, 'Request Approval Purchase Order #' . date('ymdHi'));

            foreach ($list_purchase_order as $purchase_order) {
                formulir_update_token($purchase_order->formulir, $token);
            }
        }
    }

    /**
     * @param  Request $request
     * @param  $id
     * @return mixed
     */
    public function approve(Request $request, $id) {
        $purchase_order   = PurchaseOrder::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token            = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($purchase_order->formulir, $approval_message, 'approval.point.purchasing.service.purchase.order', $token);
        // timeline_publish('approve', $purchase_order->formulir->form_number . ' approved', $this->getUserForTimeline($request, $purchase_order->formulir->approval_to));

        DB::commit();

        gritter_success('Form approved', false);

        return $this->getRedirectLink($request, $purchase_order->formulir);

    }

    /**
     * @param  Request $request
     * @param  $id
     * @return mixed
     */
    public function reject(Request $request, $id) {
        $purchase_order   = PurchaseOrder::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token            = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($purchase_order->formulir, $approval_message, 'approval.point.purchasing.service.purchase.order', $token);
        // timeline_publish('reject', 'invoice ' . $purchase_order->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $purchase_order->formulir->approval_to));

        DB::commit();

        gritter_success('Form rejected', false);

        return $this->getRedirectLink($request, $purchase_order->formulir);
    }

    /**
     * @return mixed
     */
    public function approveAll() {
        $token             = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message  = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $purchase_order = PurchaseOrder::where('formulir_id', $id)->first();
            FormulirHelper::approve($purchase_order->formulir, $approval_message, 'approval.point.purchasing.service.purchase.order', $token);
            //   timeline_publish('approve', $purchase_order->formulir->form_number . ' approved', $purchase_order->formulir->approval_to);
        }
        DB::commit();

        $view                    = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir          = \Input::get('formulir_id');

        return $view;
    }

    /**
     * @return mixed
     */
    public function rejectAll() {
        $token             = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message  = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $purchase_order = PurchaseOrder::where('formulir_id', $id)->first();
            FormulirHelper::reject($purchase_order->formulir, $approval_message, 'approval.point.purchasing.service.purchase.order', $token);
            //   timeline_publish('reject', $purchase_order->formulir->form_number . ' rejected', $purchase_order->formulir->approval_to);
        }
        DB::commit();

        $view                    = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir          = \Input::get('formulir_id');

        return $view;
    }
}
<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;

class PurchaseRequisitionApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.requisition');

        $view = view('point-purchasing::app.purchasing.point.inventory.purchase-requisition.request-approval');
        $view->list_purchase_requisition = PurchaseRequisition::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.requisition');
        $list_approver = PurchaseRequisition::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_purchase_requisition = PurchaseRequisition::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_purchase_requisition as $purchase_requisition) {
                array_push($array_formulir_id, $purchase_requisition->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_purchase_requisition,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
                ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-purchasing::emails.purchasing.point.approval.purchase-requisition', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval purchase requisition #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_purchase_requisition as $purchase_requisition) {
                formulir_update_token($purchase_requisition->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $purchase_requisition = PurchaseRequisition::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($purchase_requisition->formulir, $approval_message, 'approval.point.purchasing.requisition', $token);
        timeline_publish('approve', $purchase_requisition->formulir->form_number . ' approved', $this->getUserForTimeline($request, $purchase_requisition->formulir->approval_to));
        
        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $purchase_requisition->formulir);
    }

    public function reject(Request $request, $id)
    {
        $purchase_requisition = PurchaseRequisition::find($id);
        $approval_message = $request->input('approval_message') ? : '';
        $token = $request->input('token');

        DB::beginTransaction();

        FormulirHelper::reject($purchase_requisition->formulir, $approval_message, 'approval.point.purchasing.requisition', $token);
        timeline_publish('reject', 'purchase requisition ' . $purchase_requisition->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $purchase_requisition->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $purchase_requisition->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $purchase_requisition = PurchaseRequisition::where('formulir_id', $id)->first();
            FormulirHelper::approve($purchase_requisition->formulir, $approval_message, 'approval.point.purchasing.requisition', $token);
            timeline_publish('approve', $purchase_requisition->formulir->form_number . ' approved', $purchase_requisition->formulir->approval_to);
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
            $purchase_requisition = PurchaseRequisition::where('formulir_id', $id)->first();
            FormulirHelper::reject($purchase_requisition->formulir, $approval_message, 'approval.point.purchasing.requisition', $token);
            timeline_publish('reject', $purchase_requisition->formulir->form_number . ' rejected', $purchase_requisition->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

<?php

namespace Point\PointInventory\Http\Controllers\TransferItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointInventory\Vesa\TransferItemVesa;
use Point\PointInventory\Helpers\TransferItemHelper;
use Point\PointInventory\Helpers\ReceiveItemHelper;
use Point\PointInventory\Models\TransferItem\TransferItem;

class TransferItemApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.inventory.transfer.item');

        $view = view('point-inventory::app.inventory.point.transfer-item.send.request-approval');
        $view->listTransferItem = TransferItem::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.inventory.transfer.item');
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_transfer_item_id, $requester="VESA")
    {
        $list_approver = TransferItem::selectApproverList($list_transfer_item_id);
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_transfer_item = TransferItem::selectApproverRequest($list_transfer_item_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_transfer_item as $transfer_item) {
                array_push($array_formulir_id, $transfer_item->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_transfer_item,
                'token' => $token,
                'requester' => $requester,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            sendEmail(TransferItem::bladeEmail(), $data, $approver->email, 'Request Approval Transfer Item #' . date('ymdHi'));

            foreach ($list_transfer_item as $transfer_item) {
                formulir_update_token($transfer_item->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $transfer_item = TransferItem::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($transfer_item->formulir, $approval_message, 'approval.point.inventory.transfer.item', $token);
        TransferItemHelper::approve($transfer_item);
        timeline_publish('approval.point.inventory.transfer.item', 'Approve Transfer Item "'  . $transfer_item->formulir->form_number .'"', $this->getUserForTimeline($request, $transfer_item->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $transfer_item->formulir);
    }

    public function reject(Request $request, $id)
    {
        $transfer_item = TransferItem::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        \FormulirHelper::reject($transfer_item->formulir, $approval_message, 'approval.point.inventory.transfer.item', $token);
        timeline_publish('reject', $transfer_item->formulir->form_number.' Rejected', $this->getUserForTimeline($request, $transfer_item->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $transfer_item->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $transfer_item = TransferItem::where('formulir_id', $id)->first();
            FormulirHelper::approve($transfer_item->formulir, $approval_message, 'approval.point.purchasing.requisition', $token);
            timeline_publish('approve', $transfer_item->formulir->form_number . ' approved', $transfer_item->formulir->approval_to);
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
            $transfer_item = TransferItem::where('formulir_id', $id)->first();
            FormulirHelper::reject($transfer_item->formulir, $approval_message, 'approval.point.purchasing.requisition', $token);
            timeline_publish('reject', $transfer_item->formulir->form_number . ' rejected', $transfer_item->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

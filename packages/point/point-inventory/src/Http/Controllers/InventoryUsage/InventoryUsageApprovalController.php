<?php

namespace Point\PointInventory\Http\Controllers\InventoryUsage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointInventory\Helpers\InventoryUsageHelper;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;

class InventoryUsageApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.inventory.usage');

        $view = view('point-inventory::app.inventory.point.inventory-usage.request-approval');
        $view->listInventoryUsage = InventoryUsage::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.inventory.usage');

        $list_approver = InventoryUsage::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_inventory_usage = InventoryUsage::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_inventory_usage as $inventory_usage) {
                array_push($array_formulir_id, $inventory_usage->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_inventory_usage,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-inventory::emails.inventory.point.approval.inventory-usage-email', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('Request Approval Inventory Usage #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_inventory_usage as $inventory_usage) {
                formulir_update_token($inventory_usage->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $inventory_usage = InventoryUsage::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($inventory_usage->formulir, $approval_message, 'approval.point.inventory.usage', $token);
        FormulirHelper::close($inventory_usage->formulir_id);
        InventoryUsageHelper::approve($inventory_usage);
        InventoryUsageHelper::updateJournal($inventory_usage);
        timeline_publish('approval.point.inventory.usage', 'Approve Inventory Usage "'  . $inventory_usage->formulir->form_number .'"', $this->getUserForTimeline($request, $inventory_usage->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $inventory_usage->formulir);
    }

    public function reject(Request $request, $id)
    {
        $inventory_usage = InventoryUsage::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        \FormulirHelper::reject($inventory_usage->formulir, $approval_message, 'approval.point.inventory.usage', $token);
        timeline_publish('reject', $inventory_usage->formulir->form_number.' Rejected', $this->getUserForTimeline($request, $inventory_usage->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $inventory_usage->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $inventory_usage = InventoryUsage::where('formulir_id', $id)->first();
            FormulirHelper::approve($inventory_usage->formulir, $approval_message, 'approval.point.inventory.usage', $token);
            FormulirHelper::close($inventory_usage->formulir_id);
            InventoryUsageHelper::approve($inventory_usage);
            InventoryUsageHelper::updateJournal($inventory_usage);
            timeline_publish('approve', $inventory_usage->formulir->form_number . ' approved', $inventory_usage->formulir->approval_to);
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
            $inventory_usage = InventoryUsage::where('formulir_id', $id)->first();
            FormulirHelper::reject($inventory_usage->formulir, $approval_message, 'approval.point.inventory.usage', $token);
            timeline_publish('reject', $inventory_usage->formulir->form_number . ' rejected', $inventory_usage->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

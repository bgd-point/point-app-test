<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\Framework\Models\Master\Coa;
use Point\PointAccounting\Helpers\CutOffHelper;
use Point\PointAccounting\Models\CutOffInventory;

class CutOffInventoryApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.accounting.cut.off.inventory');

        $view = view('point-accounting::app.accounting.point.cut-off.inventory.request-approval');
        $view->list_cut_off = CutOffInventory::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.accounting.cut.off.inventory');

        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name, url('/'));

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_cut_off_id, $requester, $domain)
    {
        $list_approver = CutOffInventory::selectApproverList($list_cut_off_id);
        foreach ($list_approver as $data_approver) {
            $list_cut_off = CutOffInventory::selectApproverRequest($list_cut_off_id, $data_approver->approval_to);
            $approver = User::find($data_approver->approval_to);
            $token = md5(date('ymdhis'));
            $data = [
                'list_data' => $list_cut_off,
                'token' => $token,
                'requester' => $requester,
                'url' => $domain,
                'approver' => $approver
            ];

            sendEmail(CutOffInventory::bladeEmail(), $data, $approver->email, 'Request Approval Cut Off Inventory #' . date('ymdHi'));

            foreach ($list_cut_off as $cut_off) {
                formulir_update_token($cut_off->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $cut_off_inventory = CutOffInventory::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($cut_off_inventory->formulir, $approval_message, 'approval.point.accounting.cut.off.inventory', $token);
        CutOffHelper::journal($cut_off_inventory);
        timeline_publish('approve', 'cut off inventory ' . $cut_off_inventory->formulir->form_number . ' approved', $this->getUserForTimeline($request, $cut_off_inventory->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $cut_off_inventory->formulir);
    }

    public function reject(Request $request, $id)
    {
        $cut_off_inventory = CutOffInventory::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        \FormulirHelper::reject($cut_off_inventory->formulir, $approval_message, 'approval.point.accounting.cut.off.inventory', $token);
        timeline_publish('reject', 'cut off inventory ' . $cut_off_inventory->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $cut_off_inventory->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $cut_off_fixed_assets->formulir);
    }
}

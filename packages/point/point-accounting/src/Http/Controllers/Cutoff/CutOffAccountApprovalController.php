<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointAccounting\Helpers\CutOffHelper;
use Point\PointAccounting\Models\CutOffAccount;

class CutOffAccountApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.accounting.cut.off.account');

        $view = view('point-accounting::app.accounting.point.cut-off.account.request-approval');
        $view->list_cut_off = CutOffAccount::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.accounting.cut.off.account');

        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name, url('/'));

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_cut_off_id, $requester, $domain)
    {
        $list_approver = CutOffAccount::selectApproverList($list_cut_off_id);
        foreach ($list_approver as $data_approver) {
            $list_cut_off = CutOffAccount::selectApproverRequest($list_cut_off_id, $data_approver->approval_to);
            $approver = User::find($data_approver->approval_to);
            $token = md5(date('ymdhis'));
            $data = [
                'list_data' => $list_cut_off,
                'token' => $token,
                'requester' => $requester,
                'url' => $domain,
                'approver' => $approver
            ];

            sendEmail(CutOffAccount::bladeEmail(), $data, $approver->email, 'Request Approval Cut Off #' . date('ymdHi'));

            foreach ($list_cut_off as $cut_off) {
                formulir_update_token($cut_off->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $cut_off_account = CutOffAccount::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($cut_off_account->formulir, $approval_message, 'approval.point.accounting.cut.off.account', $token);
        CutOffHelper::journal($cut_off_account);
        timeline_publish('approve', 'cut off account ' . $cut_off_account->formulir->form_number . ' approved', $this->getUserForTimeline($request, $cut_off_account->formulir->approval_to));

        DB::commit();

        gritter_success('form approved');
        return $this->getRedirectLink($request, $cut_off_account->formulir);
    }

    public function reject(Request $request, $id)
    {
        $cut_off_account = CutOffAccount::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        \FormulirHelper::reject($cut_off_account->formulir, $approval_message, 'approval.point.accounting.cut.off', $token);
        timeline_publish('reject', 'cut off account ' . $cut_off_account->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $cut_off_account->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected');
        return $this->getRedirectLink($request, $cut_off_account->formulir);
    }
}

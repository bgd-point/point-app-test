<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Master\FixedAssetsContract;
use Point\Framework\Models\Master\Person;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;

class FixedAssetsContractApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.contract');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.create.request-approval');
        $view->list_contract = FixedAssetsContract::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.contract');
        $list_approver = FixedAssetsContract::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();

        foreach ($list_approver as $data_approver) {
            $list_contract = FixedAssetsContract::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $approver = User::find($data_approver->approval_to);
            $token = md5(date('ymdhis'));
            
            $data = [
                'list_data' => $list_contract,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver
            ];
            
            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-purchasing::emails.purchasing.point.approval.fixed-assets.contract', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval contract #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_contract as $contract) {
                formulir_update_token($contract->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $contract = FixedAssetsContract::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($contract->formulir, $approval_message, 'approval.point.purchasing.contract', $token);
        timeline_publish('approve', $contract->formulir->form_number . ' approved', $this->getUserForTimeline($request, $contract->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $contract->formulir);
    }

    public function reject(Request $request, $id)
    {
        $contract = FixedAssetsContract::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($contract->formulir, $approval_message, 'approval.point.purchasing.contract', $token);
        timeline_publish('reject', 'contract ' . $contract->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $contract->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $contract->formulir);
    }
}

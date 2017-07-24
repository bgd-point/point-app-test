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
use Point\PointAccounting\Models\CutOffReceivable;

class CutOffReceivableApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.accounting.cut.off.receivable');

        $view = view('point-accounting::app.accounting.point.cut-off.receivable.request-approval');
        $view->list_cut_off = CutOffReceivable::selectRequestApproval()->paginate(100);
        
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {   
        access_is_allowed('create.point.accounting.cut.off.receivable');

        if($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        $list_approver = CutOffReceivable::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();

        foreach($list_approver as $data_approver) {
            $list_cut_off = CutOffReceivable::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $approver = User::find($data_approver->approval_to);
            $token = md5(date('ymdhis'));
            $data = [
                'list_data' => $list_cut_off,
                'token' => $token, 
                'username' => auth()->user()->name, 
                'url' => url('/'),
                'approver' => $approver
            ];

            \Queue::push(function($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-accounting::emails.accounting.point.approval.cut-off-receivable', $data, function ($message) use($approver) {
                    $message->to($approver->email)->subject('Request Approval Cut Off Account Receivable #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_cut_off as $cut_off) {
                formulir_update_token($cut_off->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }
    public function approve(Request $request, $id)
    {
        $cut_off_receivable = CutOffReceivable::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($cut_off_receivable->formulir, $approval_message, 'approval.point.accounting.cut.off.receivable', $token);
        CutOffHelper::journal($cut_off_receivable);
        timeline_publish('approve', 'cut off account receivable ' . $cut_off_receivable->formulir->form_number . ' approved', $this->getUserForTimeline($request, $cut_off_receivable->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $cut_off_receivable->formulir);
    }

    public function reject(Request $request, $id)
    {
        $cut_off_receivable = CutOffReceivable::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        \FormulirHelper::reject($cut_off_receivable->formulir, $approval_message, 'approval.point.accounting.cut.off.receivable', $token);
        timeline_publish('reject', 'cut off account receivable' . $cut_off_receivable->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $cut_off_receivable->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $cut_off_receivable->formulir);
    }
}

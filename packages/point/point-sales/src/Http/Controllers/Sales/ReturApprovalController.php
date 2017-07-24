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
use Point\PointSales\Helpers\ReturHelper;
use Point\PointSales\Models\Sales\Retur;

class ReturApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.sales.return');
        
        $view = view('point-sales::app.sales.point.sales.retur.request-approval');
        $view->list_retur = Retur::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.sales.return');
        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        $list_approver = Retur::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_retur = Retur::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_retur as $retur) {
                array_push($array_formulir_id, $retur->formulir_id);
            }
            
            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_retur' => $list_retur, 
                'token' => $token, 
                'username' => auth()->user()->name, 
                'url' => url('/'), 
                 'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($data['database_name']);
                \Mail::send('point-sales::app.emails.sales.point.approval.retur', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval retur #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_retur as $retur) {
                formulir_update_token($retur->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $retur = Retur::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();
        FormulirHelper::approve($retur->formulir, $approval_message, 'approval.point.sales.return', $token);
        ReturHelper::journal($retur);
        timeline_publish('approve', $retur->formulir->form_number.' approved', $user_approval);

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $retur->formulir);
    }

    public function reject(Request $request, $id)
    {
        $retur = Retur::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($retur->formulir, $approval_message, 'approval.point.sales.return', $token);
        timeline_publish('reject', $retur->formulir->form_number.' rejected', $user_approval);

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $retur->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $retur = Retur::where('formulir_id', $id)->first();
            FormulirHelper::approve($retur->formulir, $approval_message, 'approval.point.sales.retur', $token);
            timeline_publish('approve', $retur->formulir->form_number . ' approved', $retur->formulir->approval_to);
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
            $retur = Retur::where('formulir_id', $id)->first();
            FormulirHelper::reject($retur->formulir, $approval_message, 'approval.point.sales.retur', $token);
            timeline_publish('reject', $retur->formulir->form_number . ' rejected', $retur->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

<?php

namespace Point\Ksp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use App\Http\Controllers\Controller;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\Ksp\Models\LoanApplication;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;

class LoanApplicationApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.ksp.loan.application');

        $view = view('ksp::app.facility.ksp.loan-application.request-approval');
        $view->list_loan_application = LoanApplication::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.ksp.loan.application');
        $list_approver = LoanApplication::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_loan_application = LoanApplication::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_loan_application as $loan_application) {
                array_push($array_formulir_id, $loan_application->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_loan_application,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('ksp::emails.facility.ksp.approval.loan-application', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval purchase requisition #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_loan_application as $loan_application) {
                formulir_update_token($loan_application->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $loan_application = LoanApplication::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($loan_application->formulir, $approval_message, 'approval.ksp.loan.application', $token);
        timeline_publish('approve', $loan_application->formulir->form_number . ' approved', $this->getUserForTimeline($request, $loan_application->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $loan_application->formulir);
    }

    public function reject(Request $request, $id)
    {
        $loan_application = LoanApplication::find($id);
        $approval_message = $request->input('approval_message') ? : '';
        $token = $request->input('token');

        DB::beginTransaction();

        FormulirHelper::reject($loan_application->formulir, $approval_message, 'approval.ksp.loan.application', $token);
        timeline_publish('reject', 'purchase requisition ' . $loan_application->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $loan_application->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $loan_application->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $loan_application = LoanApplication::where('formulir_id', $id)->first();
            FormulirHelper::approve($loan_application->formulir, $approval_message, 'approval.ksp.loan.application', $token);
            timeline_publish('approve', $loan_application->formulir->form_number . ' approved', $loan_application->formulir->approval_to);
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
            $loan_application = LoanApplication::where('formulir_id', $id)->first();
            FormulirHelper::reject($loan_application->formulir, $approval_message, 'approval.ksp.loan.application', $token);
            timeline_publish('reject', $loan_application->formulir->form_number . ' rejected', $loan_application->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

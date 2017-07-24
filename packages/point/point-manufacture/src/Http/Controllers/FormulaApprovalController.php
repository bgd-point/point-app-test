<?php

namespace Point\PointManufacture\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointManufacture\Vesa\InputAfterAprrovalVesa;
use Point\PointManufacture\Models\Formula;
use Point\PointManufacture\Helpers\ManufactureHelper;
use Point\PointManufacture\Models\Process;

class FormulaApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.manufacture.formula');

        $view = view('point-manufacture::app.manufacture.point.formula.request-approval');
        $view->list_formula = Formula::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.manufacture.formula');

        $list_approver = Formula::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_data = Formula::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_data as $formula) {
                array_push($array_formulir_id, $formula->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_data,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-manufacture::emails.manufacture.point.approval.formula', $data,
                    function ($message) use ($approver) {
                        $message->to($approver->email)->subject('request approval manufacture formula #' . date('ymdHi'));
                    });
                $job->delete();
            });

            foreach ($list_data as $formula) {
                formulir_update_token($formula->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $input_approval = Formula::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        FormulirHelper::approve($input_approval->formulir, $approval_message, 'approval.point.manufacture.formula', $token);
        timeline_publish('approve', 'proses input ' . $input_approval->formulir->form_number . ' approved', $this->getUserForTimeline($request, $input_approval->formulir->approval_to));

        \DB::commit();

        gritter_success('form approved');
        return $this->getRedirectLink($request, $input_approval->formulir);
    }

    public function reject(Request $request, $id)
    {
        $input_approval = Formula::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        FormulirHelper::reject($input_approval, $approval_message, 'approval.point.manufacture.formula', $token);
        timeline_publish('reject', 'proses input ' . $input_approval->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $input_approval->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected');
        return $this->getRedirectLink($request, $input_approval->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        \DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $formula = Formula::where('formulir_id', $id)->first();
            FormulirHelper::approve($formula->formulir, $approval_message, 'approval.point.manufacture.formula', $token);
            timeline_publish('approve', $formula->formulir->form_number . ' approved', $formula->formulir->approval_to);
        }
        \DB::commit();

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

        \DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $formula = Formula::where('formulir_id', $id)->first();
            FormulirHelper::reject($formula->formulir, $approval_message, 'approval.point.manufacture.formula', $token);
            timeline_publish('reject', $formula->formulir->form_number . ' rejected', $formula->formulir->approval_to);
        }
        \DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

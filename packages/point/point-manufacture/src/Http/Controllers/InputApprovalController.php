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
use Point\PointManufacture\Models\InputProcess;
use Point\PointManufacture\Helpers\ManufactureHelper;
use Point\PointManufacture\Models\Process;

class InputApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval($process_id)
    {
        access_is_allowed('create.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.request-approval');
        $view->list_input = InputProcess::selectRequestApproval()->where('process_id', $process_id)->paginate(100);
        $view->process = Process::find($process_id);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.manufacture.input');
        
        if (count(\Input::get('formulir_id')) == 0) {
            gritter_success('please select at least one form to request an approval');
            return redirect()->back();
        }

        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_process_in_id, $requester="VESA")
    {
        $list_approver = InputProcess::selectApproverList($list_process_in_id);
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_process_in = InputProcess::selectApproverRequest($list_process_in_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_process_in as $process) {
                array_push($array_formulir_id, $process->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_process_in,
                'token' => $token,
                'requester' => $requester,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            sendEmail(InputProcess::bladeEmail(), $data, $approver->email, 'Request Approval Manufacture Process In #' . date('ymdHi'));

            foreach ($list_process_in as $process_in) {
                formulir_update_token($process_in->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $input_approval = InputProcess::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        FormulirHelper::approve($input_approval->formulir, $approval_message, 'approval.point.manufacture.input', $token);
        ManufactureHelper::approveInput($input_approval);
        timeline_publish('approve', 'proses input ' . $input_approval->formulir->form_number . ' approved', $this->getUserForTimeline($request, $input_approval->formulir->approval_to));

        \DB::commit();

        gritter_success('form approved');
        return $this->getRedirectLink($request, $input_approval->formulir);
    }

    public function reject(Request $request, $id)
    {
        $input_approval = InputProcess::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        FormulirHelper::reject($input_approval, $approval_message, 'approval.point.manufacture.input', $token);
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
            $input_process = InputProcess::where('formulir_id', $id)->first();
            ManufactureHelper::approveInput($input_process);
            FormulirHelper::approve($input_process->formulir, $approval_message, 'approval.point.manufacture.input', $token);
            timeline_publish('approve', $input_process->formulir->form_number . ' approved', $input_process->formulir->approval_to);
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
            $input_process = InputProcess::where('formulir_id', $id)->first();
            FormulirHelper::reject($input_process->formulir, $approval_message, 'approval.point.manufacture.input', $token);
            timeline_publish('reject', $input_process->formulir->form_number . ' rejected', $input_process->formulir->approval_to);
        }
        \DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

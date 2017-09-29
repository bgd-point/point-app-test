<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Master\Person;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointExpedition\Models\Downpayment;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;

class DownpaymentApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.expedition.downpayment');

        $view = view('point-expedition::app.expedition.point.downpayment.request-approval');
        $view->list_downpayment = Downpayment::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.expedition.downpayment');
        $list_approver = Downpayment::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_downpayment = Downpayment::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_downpayment as $downpayment) {
                array_push($array_formulir_id, $downpayment->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_downpayment,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id

            ];
            
            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-expedition::emails.expedition.point.approval.downpayment', $data, function ($message) use ($approver) {
                        $message->to($approver->email)->subject('request approval downpayment #' . date('ymdHi'));
                    });
                $job->delete();
            });

            foreach ($list_downpayment as $downpayment) {
                formulir_update_token($downpayment->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $downpayment = Downpayment::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($downpayment->formulir, $approval_message, 'approval.point.expedition.downpayment', $token);
        self::paymentReference($downpayment);
        timeline_publish('approve', $downpayment->formulir->form_number . ' approved', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));

        DB::commit();

        gritter_success('form approved');
        return $this->getRedirectLink($request, $downpayment->formulir);
    }

    public function reject(Request $request, $id)
    {
        $downpayment = Downpayment::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($downpayment->formulir, $approval_message, 'approval.point.expedition.downpayment', $token);
        timeline_publish('reject', 'downpayment ' . $downpayment->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected');
        return $this->getRedirectLink($request, $downpayment->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $downpayment = Downpayment::where('formulir_id', $id)->first();
            FormulirHelper::approve($downpayment->formulir, $approval_message, 'approval.point.expedition.downpayment', $token);
            self::paymentReference($downpayment);
            timeline_publish('approve', $downpayment->formulir->form_number . ' approved', $downpayment->formulir->approval_to);
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
            $downpayment = Downpayment::where('formulir_id', $id)->first();
            FormulirHelper::reject($downpayment->formulir, $approval_message, 'approval.point.expedition.downpayment', $token);
            timeline_publish('reject', $downpayment->formulir->form_number . ' rejected', $downpayment->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }

    public static function paymentReference($downpayment)
    {
        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $downpayment->formulir->id;
        $payment_reference->person_id = $downpayment->expedition_id;
        $payment_reference->payment_flow = 'out';
        $payment_reference->payment_type = $downpayment->payment_type;
        $payment_reference->total = $downpayment->amount;
        $payment_reference->save();

        $payment_reference_detail = new PaymentReferenceDetail;
        $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
        $payment_reference_detail->coa_id = JournalHelper::getAccount('point expedition', 'expedition downpayment');
        $payment_reference_detail->allocation_id = 1;
        $payment_reference_detail->notes_detail = $downpayment->formulir->notes;
        $payment_reference_detail->amount = $downpayment->amount;
        $payment_reference_detail->form_reference_id = $downpayment->formulir->id;
        $payment_reference_detail->subledger_id = $downpayment->expedition_id;
        $payment_reference_detail->subledger_type = get_class(new Person);
        $payment_reference_detail->save();
    }
}

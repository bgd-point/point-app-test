<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

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
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;
use Point\PointPurchasing\Models\Service\Downpayment;

class DownpaymentApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.service.downpayment');

        $view = view('point-purchasing::app.purchasing.point.service.downpayment.request-approval');
        $view->list_downpayment = Downpayment::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.service.downpayment');
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect()->back();
    }
    public static function sendingRequestApproval($list_downpayment_id, $requester, $domain=url('/'))
    {
        $list_approver = Downpayment::selectApproverList($list_downpayment_id);
        $token = md5(date('ymdhis'));
        
        foreach ($list_approver as $data_approver) {
            $list_downpayment = Downpayment::selectApproverRequest($list_downpayment_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_downpayment as $downpayment) {
                array_push($array_formulir_id, $downpayment->formulir_id);
            }
            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_downpayment' => $list_downpayment,
                'token' => $token,
                'requester' => $requester,
                'url' => $domain,
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];
            
            sendEmail(Downpayment::bladeEmail(), $data, $approver->email, 'Request Approval Downpayment #' . date('ymdHi'));

            foreach ($list_downpayment as $downpayment) {
                formulir_update_token($downpayment->formulir, $token);
            }
        }

    }

    public function approve(Request $request, $id)
    {
        $downpayment = Downpayment::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($downpayment->formulir, $approval_message, 'approval.point.purchasing.service.downpayment', $token);
        self::addPaymentReference($downpayment);
        timeline_publish('approve', $downpayment->formulir->form_number . ' approved', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $downpayment->formulir);
    }

    public function reject(Request $request, $id)
    {
        $downpayment = Downpayment::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($downpayment->formulir, $approval_message, 'approval.point.purchasing.service.downpayment', $token);
        timeline_publish('reject', 'downpayment ' . $downpayment->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
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
            FormulirHelper::approve($downpayment->formulir, $approval_message, 'approval.point.purchasing.service.downpayment', $token);
            self::addPaymentReference($downpayment);
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
            FormulirHelper::reject($downpayment->formulir, $approval_message, 'approval.point.purchasing.service.downpayment', $token);
            timeline_publish('reject', $downpayment->formulir->form_number . ' rejected', $downpayment->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }

    private static function addPaymentReference($downpayment)
    {
        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $downpayment->formulir->id;
        $payment_reference->person_id = $downpayment->supplier_id;
        $payment_reference->payment_flow = 'out';
        $payment_reference->payment_type = $downpayment->payment_type;
        $payment_reference->total = $downpayment->amount;
        $payment_reference->save();

        $purchasing_downpayment_account = JournalHelper::getAccount('point purchasing', 'purchase downpayment');
        $payment_reference_detail = new PaymentReferenceDetail;
        $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
        $payment_reference_detail->coa_id = $purchasing_downpayment_account;
        $payment_reference_detail->notes_detail;
        $payment_reference_detail->amount = $downpayment->amount;
        $payment_reference_detail->allocation_id = 1;
        $payment_reference_detail->notes_detail = $downpayment->formulir->notes;
        $payment_reference_detail->form_reference_id = $downpayment->formulir->id;
        $payment_reference_detail->subledger_id = $downpayment->supplier_id;
        $payment_reference_detail->subledger_type = get_class(new Person);
        $payment_reference_detail->save();
    }
}

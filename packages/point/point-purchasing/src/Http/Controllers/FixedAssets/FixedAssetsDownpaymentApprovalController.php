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
use Point\Framework\Models\Master\Person;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsDownpayment;

class FixedAssetsDownpaymentApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.downpayment.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.downpayment.request-approval');
        $view->list_downpayment = FixedAssetsDownpayment::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.downpayment.fixed.assets');
        $list_approver = FixedAssetsDownpayment::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();

        foreach ($list_approver as $data_approver) {
            $list_downpayment = FixedAssetsDownpayment::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $approver = User::find($data_approver->approval_to);
            $token = md5(date('ymdhis'));
            
            $data = [
                'list_data' => $list_downpayment,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver
            ];
            
            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-purchasing::emails.purchasing.point.approval.fixed-assets.downpayment', $data, function ($message) use ($approver) {
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
        $downpayment = FixedAssetsDownpayment::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($downpayment->formulir, $approval_message, 'approval.point.purchasing.downpayment.fixed.assets', $token);
        self::addPaymentReference($request, $downpayment);
        timeline_publish('approve', $downpayment->formulir->form_number . ' approved', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $downpayment->formulir);
    }

    public function reject(Request $request, $id)
    {
        $downpayment = FixedAssetsDownpayment::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($downpayment->formulir, $approval_message, 'approval.point.purchasing.downpayment.fixed.assets', $token);
        timeline_publish('reject', 'downpayment ' . $downpayment->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $downpayment->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $downpayment->formulir);
    }

    public static function addPaymentReference($request, $downpayment)
    {
        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $downpayment->formulir_id;
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
        $payment_reference_detail->form_reference_id = $downpayment->id;
        $payment_reference_detail->subledger_id = $downpayment->supplier_id;
        $payment_reference_detail->subledger_type = get_class(new Person);
        $payment_reference_detail->save();
    }
}

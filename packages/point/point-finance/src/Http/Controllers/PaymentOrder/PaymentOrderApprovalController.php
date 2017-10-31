<?php

namespace Point\PointFinance\Http\Controllers\PaymentOrder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointFinance\Models\PaymentOrder\PaymentOrder;
use Point\PointFinance\Http\Controllers\Controller;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;

class PaymentOrderApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.finance.payment.order');

        $view = view('point-finance::app.finance.point.payment-order.request-approval');
        $view->list_payment_order = PaymentOrder::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.finance.payment.order');

        $list_approver = PaymentOrder::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_payment_order = PaymentOrder::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_payment_order as $payment_order) {
                array_push($array_formulir_id, $payment_order->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_payment_order,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-finance::emails.finance.point.approval.payment-order', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request an approval for payment order #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_payment_order as $payment_order) {
                formulir_update_token($payment_order->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $payment_order = PaymentOrder::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        self::insertPaymentReference($payment_order, $token, $approval_message);

        timeline_publish('approval.point.finance.payment.order', 'Approve Payment Order "'  . $payment_order->formulir->form_number .'"', $this->getUserForTimeline($request, $payment_order->formulir->approval_to));
        gritter_success('form approved', false);

        return $this->getRedirectLink($request, $payment_order->formulir);
    }

    public function insertPaymentReference($payment_order, $token, $approval_message)
    {
        DB::beginTransaction();

        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $payment_order->formulir_id;
        $payment_reference->person_id = $payment_order->person_id;
        $payment_reference->payment_flow = 'out';
        $payment_reference->payment_type = $payment_order->payment_type;
        $payment_reference->save();

        $total = 0;

        foreach ($payment_order->detail as $payment_order_detail) {
            $total += $payment_order_detail->amount;
            $payment_reference_detail = new PaymentReferenceDetail;
            $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
            $payment_reference_detail->coa_id = $payment_order_detail->coa_id;
            $payment_reference_detail->allocation_id = $payment_order_detail->allocation_id;
            $payment_reference_detail->notes_detail = $payment_order_detail->notes_detail;
            $payment_reference_detail->amount = $payment_order_detail->amount;
            $payment_reference_detail->save();
        }

        $payment_reference->total = $total;

        $payment_reference->save();

        FormulirHelper::approve($payment_order->formulir, $approval_message, 'approval.point.finance.payment.order', $token);

        DB::commit();
    }

    public function reject(Request $request, $id)
    {
        $payment_order = PaymentOrder::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::reject($payment_order->formulir, $approval_message, 'approval.point.finance.payment.order', $token);
        timeline_publish('approval.point.finance.payment.order', 'successfully reject'  . $payment_order->formulir->form_number, $this->getUserForTimeline($request, $payment_order->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $payment_order->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        foreach ($array_formulir_id as $id) {
            $payment_order = PaymentOrder::where('formulir_id', $id)->first();
            self::insertPaymentReference($payment_order, $token, $approval_message);
            timeline_publish('approve', $payment_order->formulir->form_number . ' approved', $payment_order->formulir->approval_to);
        }

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
            $payment_order = PaymentOrder::where('formulir_id', $id)->first();
            FormulirHelper::reject($payment_order->formulir, $approval_message, 'approval.point.finance.payment.order', $token);
            timeline_publish('reject', $payment_order->formulir->form_number . ' rejected', $payment_order->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Person;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;
use Point\PointPurchasing\Models\Inventory\PaymentOrder;

class PaymentOrderApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.payment.order');
        
        $view = view('point-purchasing::app.purchasing.point.inventory.payment-order.request-approval');
        $view->list_payment_order = PaymentOrder::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.payment.order');
        
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
                \Mail::send('point-purchasing::emails.purchasing.point.approval.payment-order', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval payment order #' . date('ymdHi'));
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
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($payment_order->formulir, $approval_message, 'approval.point.purchasing.payment.order', $token);
        self::addPaymentReference($payment_order);
        timeline_publish('approve', $payment_order->formulir->form_number . ' approved', $this->getUserForTimeline($request, $payment_order->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $payment_order->formulir);
    }

    public function reject(Request $request, $id)
    {
        $payment_order = PaymentOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($payment_order->formulir, $approval_message, 'approval.point.purchasing.payment.order', $token);
        timeline_publish('reject', 'payment order ' . $payment_order->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $payment_order->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $payment_order->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $payment_order = PaymentOrder::where('formulir_id', $id)->first();
            FormulirHelper::approve($payment_order->formulir, $approval_message, 'approval.point.purchasing.payment.order', $token);
            self::addPaymentReference($payment_order);
            timeline_publish('approve', $payment_order->formulir->form_number . ' approved', $payment_order->formulir->approval_to);
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
            $payment_order = PaymentOrder::where('formulir_id', $id)->first();
            FormulirHelper::reject($payment_order->formulir, $approval_message, 'approval.point.purchasing.payment.order', $token);
            timeline_publish('reject', $payment_order->formulir->form_number . ' rejected', $payment_order->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
    
    public function addPaymentReference($payment_order)
    {
        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $payment_order->formulir_id;
        $payment_reference->person_id = $payment_order->supplier_id;
        $payment_reference->payment_flow = 'out';
        $payment_reference->payment_type = $payment_order->payment_type;
        $payment_reference->save();

        $total = 0;
        foreach ($payment_order->details as $payment_order_detail) {
            $payment_reference_detail = new PaymentReferenceDetail;
            $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
            $payment_reference_detail->coa_id = $payment_order_detail->coa_id;
            $payment_reference_detail->allocation_id = 1;
            $payment_reference_detail->notes_detail = $payment_order_detail->detail_notes;
            $payment_reference_detail->amount = $payment_order_detail->amount;
            $payment_reference_detail->form_reference_id = $payment_order_detail->form_reference_id;
            $payment_reference_detail->subledger_id = $payment_order->supplier_id;
            $payment_reference_detail->subledger_type = get_class(new Person);
            $payment_reference_detail->reference_id = $payment_order_detail->reference_id;
            $payment_reference_detail->reference_type = $payment_order_detail->reference_type;
            $payment_reference_detail->save();

            $total += $payment_order_detail->amount;
        }

        foreach ($payment_order->others as $payment_order_other) {
            $payment_reference_detail = new PaymentReferenceDetail;
            $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
            $payment_reference_detail->coa_id = $payment_order_other->coa_id;
            $payment_reference_detail->allocation_id = $payment_order_other->allocation_id;
            $payment_reference_detail->notes_detail = $payment_order_other->other_notes;
            $payment_reference_detail->amount = $payment_order_other->amount;
            $payment_reference_detail->save();
            
            $total += $payment_reference_detail->amount;

            // Insert to Allocation Report
            AllocationHelper::save($payment_order->formulir->id, $payment_order_other->allocation_id, $payment_order_other->amount, $payment_reference_detail->notes_detail);
        }
        
        $payment_reference->total = $total;
        $payment_reference->save();
    }
}

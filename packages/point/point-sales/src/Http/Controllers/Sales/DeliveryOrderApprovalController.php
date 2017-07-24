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
use Point\PointSales\Models\Sales\DeliveryOrder;

class DeliveryOrderApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.sales.delivery.order');

        $view = view('point-sales::app.sales.point.sales.delivery-order.request-approval');
        $view->list_delivery_order = DeliveryOrder::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.sales.delivery.order');
        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        $list_approver = DeliveryOrder::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_delivery_order = DeliveryOrder::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_delivery_order as $delivery_order) {
                array_push($array_formulir_id, $delivery_order->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_delivery_order,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
                ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-sales::app.emails.sales.point.approval.delivery-order', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval delivery order #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_delivery_order as $delivery) {
                formulir_update_token($delivery->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $delivery_order = DeliveryOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($delivery_order->formulir, $approval_message, 'approval.point.sales.delivery.order', $token);
        timeline_publish('approve', 'delivery order ' . $delivery_order->formulir->form_number . ' approved', $this->getUserForTimeline($request, $delivery_order->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $delivery_order->formulir);
    }

    public function reject(Request $request, $id)
    {
        $delivery_order = DeliveryOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($delivery_order->formulir, $approval_message, 'approval.point.sales.delivery.order', $token);
        timeline_publish('reject', 'delivery order ' . $delivery_order->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $delivery_order->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $delivery_order->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $delivery_order = DeliveryOrder::where('formulir_id', $id)->first();
            FormulirHelper::approve($delivery_order->formulir, $approval_message, 'approval.point.sales.delivery.order', $token);
            timeline_publish('approve', $delivery_order->formulir->form_number . ' approved', $delivery_order->formulir->approval_to);
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
            $delivery_order = DeliveryOrder::where('formulir_id', $id)->first();
            FormulirHelper::reject($delivery_order->formulir, $approval_message, 'approval.point.sales.delivery.order', $token);
            timeline_publish('reject', $delivery_order->formulir->form_number . ' rejected', $delivery_order->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

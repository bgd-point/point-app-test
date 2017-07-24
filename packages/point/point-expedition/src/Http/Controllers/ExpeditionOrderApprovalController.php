<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\PointExpedition\Models\ExpeditionOrder;

class ExpeditionOrderApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.expedition.order');

        $view = view('point-expedition::app.expedition.point.expedition-order.request-approval');
        $view->list_expedition_order = ExpeditionOrder::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.expedition.order');

        $list_approver = ExpeditionOrder::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_expedition_order = ExpeditionOrder::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_expedition_order as $expedition_order) {
                array_push($array_formulir_id, $expedition_order->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_expedition_order,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];
            
            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-expedition::emails.expedition.point.approval.expedition-order', $data,
                    function ($message) use ($approver) {
                        $message->to($approver->email)->subject('request approval expedition order #' . date('ymdHi'));
                    });
                $job->delete();
            });

            foreach ($list_expedition_order as $expedition_order) {
                formulir_update_token($expedition_order->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $expedition_order = ExpeditionOrder::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');
        FormulirHelper::approve($expedition_order->formulir, $approval_message, 'approval.point.expedition.order', $token);
        timeline_publish('approve', $expedition_order->formulir->form_number . ' approved', $this->getUserForTimeline($request, $expedition_order->formulir->approval_to));

        DB::commit();

        gritter_success('form approved');

        return $this->getRedirectLink($request, $expedition_order->formulir);
    }

    public function reject(Request $request, $id)
    {
        $expedition_order = ExpeditionOrder::find($id);
        $approval_message = \Input::get('approval_message') ?: '';
        $token = \Input::get('token');
        
        DB::beginTransaction();

        FormulirHelper::reject($expedition_order->formulir, $approval_message, 'approval.point.expedition.order', $token);
        timeline_publish('reject', 'expedition order ' . $expedition_order->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $expedition_order->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected');

        return $this->getRedirectLink($request, $expedition_order->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $expedition_order = ExpeditionOrder::where('formulir_id', $id)->first();
            FormulirHelper::approve($expedition_order->formulir, $approval_message, 'approval.point.expedition.order', $token);
            timeline_publish('approve', $expedition_order->formulir->form_number . ' approved', $expedition_order->formulir->approval_to);
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
            $expedition_order = ExpeditionOrder::where('formulir_id', $id)->first();
            FormulirHelper::reject($expedition_order->formulir, $approval_message, 'approval.point.expedition.order', $token);
            timeline_publish('reject', $expedition_order->formulir->form_number . ' rejected', $expedition_order->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

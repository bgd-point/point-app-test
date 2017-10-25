<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;

class FixedAssetsGoodsReceivedApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.goods.received.fixed.assets');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.goods-received.request-approval');
        $view->list_goods_received = FixedAssetsGoodsReceived::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.goods.received.fixed.assets');
        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        $list_approver = FixedAssetsGoodsReceived::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_goods_received = FixedAssetsGoodsReceived::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_goods_received as $goods_received) {
                array_push($array_formulir_id, $goods_received->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_goods_received' => $list_goods_received,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
                ];
            
            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-purchasing::emails.purchasing.point.approval.fixed-assets.goods-received', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval goods received #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_goods_received as $delivery) {
                formulir_update_token($delivery->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $delivery_order = FixedAssetsGoodsReceived::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($delivery_order->formulir, $approval_message, 'approval.point.purchasing.goods.received.fixed.assets', $token);
        timeline_publish('approve', 'goods received ' . $delivery_order->formulir->form_number . ' approved', $this->getUserForTimeline($request, $delivery_order->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $delivery_order->formulir);
    }

    public function reject(Request $request, $id)
    {
        $delivery_order = FixedAssetsGoodsReceived::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($delivery_order->formulir, $approval_message, 'approval.point.purchasing.goods.received.fixed.assets', $token);
        timeline_publish('reject', 'goods received ' . $delivery_order->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $delivery_order->formulir->approval_to));

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
            $goods_received = FixedAssetsGoodsReceived::where('formulir_id', $id)->first();
            FormulirHelper::approve($goods_received->formulir, $approval_message, 'approval.point.purchasing.goods.received.fixed.assets', $token);
            timeline_publish('approve', $goods_received->formulir->form_number . ' approved', $goods_received->formulir->approval_to);
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
            $goods_received = FixedAssetsGoodsReceived::where('formulir_id', $id)->first();
            FormulirHelper::reject($goods_received->formulir, $approval_message, 'approval.point.purchasing.goods.received.fixed.assets', $token);
            timeline_publish('reject', $goods_received->formulir->form_number . ' rejected', $goods_received->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\User;
use Point\Core\Models\Vesa;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\PointPurchasing\Helpers\ReturHelper;
use Point\PointPurchasing\Models\Inventory\Retur;

class ReturApprovalController extends Controller
{
    use ValidationTrait;

    public function approve(Request $request, $id)
    {
        $retur = Retur::find($id);

        $user_approval = $retur->formulir->approval_to;
        if ($request->method() == 'POST') {
            access_is_allowed('approval.point.purchasing.return');
            $user_approval = auth()->user()->id;
        }

        DB::beginTransaction();
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');
        FormulirHelper::approve($retur->formulir, $approval_message, 'approval.point.purchasing.return', $token);
        ReturHelper::journal($retur);
        timeline_publish('approve', $retur->formulir->form_number.' approved', $user_approval);

        DB::commit();

        gritter_success('form approved', false);

        if ($request->method() == 'POST') {
            return redirect()->back();
        } else {
            return view('framework::app.approval-status')->with('formulir', $retur->formulir);
        }
    }

    public function reject(Request $request, $id)
    {
        $retur = Retur::find($id);
        $user_approval = $retur->formulir->approval_to;
        if ($request->method() == 'POST') {
            access_is_allowed('approval.point.purchasing.return');
            $user_approval = auth()->user()->id;
        }

        DB::beginTransaction();
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        FormulirHelper::reject($retur->formulir, $approval_message, 'approval.point.purchasing.return', $token);
        timeline_publish('reject', $retur->formulir->form_number.' rejected', $user_approval);

        DB::commit();

        gritter_success('form rejected', false);
        if ($request->method() == 'POST') {
            return redirect()->back();
        } else {
            return view('framework::app.approval-status')->with('formulir', $retur->formulir);
        }
    }

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.return');
        
        $view = view('point-purchasing::app.purchasing.point.inventory.retur.request-approval');
        $view->list_retur = Retur::joinFormulir()->notArchived()->where('form_status', '=', 0)->where('approval_status', '=', 0)->selectOriginal()->paginate(100);
        
        return $view;
    }

    public function sendRequestApproval()
    {
        access_is_allowed('create.point.purchasing.return');
        
        $list_approver = Retur::joinFormulir()
            ->notArchived()
            ->whereIn('formulir_id', \Input::get('formulir_id'))
            ->groupBy('formulir.approval_to')
            ->select('formulir.approval_to as approval_to')
            ->get();

        foreach ($list_approver as $data_approver) {
            $list_retur = Retur::joinFormulir()
                ->notArchived()
                ->whereIn('formulir_id', \Input::get('formulir_id'))
                ->where('approval_to', '=', $data_approver->approval_to)
                ->selectOriginal()
                ->get();

            $approver = User::find($data_approver->approval_to);
            $token = md5(date('ymdhis'));
            $data = ['list_retur' => $list_retur, 'token' => $token, 'username' => auth()->user()->name, 'url' => url('/')];

            \Queue::push(function ($job) use ($approver, $data) {
                \Mail::send('point-purchasing::emails.purchasing.point.approval.retur', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval retur #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_retur as $retur) {
                $retur->formulir->request_approval_at = \Carbon::now();
                $retur->formulir->request_approval_token = $token;
                $retur->formulir->save();
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }
}

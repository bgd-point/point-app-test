<?php

namespace Point\BumiShares\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;

use Point\Framework\Helpers\FormulirHelper;

use Point\BumiShares\Helpers\SharesStockHelper as StockHelper;
use Point\BumiShares\Models\Sell;

use App\Http\Controllers\Controller;
use Point\Framework\Traits\RequestApprovalTrait;

class SellApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function approve(Request $request, $id)
    {
        $shares_sell = Sell::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($shares_sell->formulir, $approval_message, 'approval.bumi.shares.sell', $token);
        StockHelper::out($shares_sell);
        $shares_sell->formulir->form_status = 1;
        $shares_sell->formulir->save();
        timeline_publish('approval.bumi.shares.sell', 'approve sell shares '. $shares_sell->shares->name . ' number ' . $shares_sell->formulir->form_number, $this->getUserForTimeline($request, $shares_sell->formulir->approval_to));
        DB::commit();

        gritter_success('form approved', false);

        if ($request->method() == 'POST') {
            return redirect()->back();
        } else {
            return view('framework::app.approval-status')->with('formulir', $shares_sell->formulir);
        }
    }

    public function reject(Request $request, $id)
    {
        $shares_sell = Sell::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::reject($shares_sell->formulir, $approval_message, 'approval.bumi.shares.sell', $token);

        $shares_sell->formulir->form_status = -1;
        $shares_sell->formulir->save();
        timeline_publish('approval.bumi.shares.sell', 'reject sell shares '. $shares_sell->shares->name . ' number ' . $shares_sell->formulir->form_number, $this->getUserForTimeline($request, $shares_sell->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);

        if ($request->method() == 'POST') {
            return redirect()->back();
        } else {
            return view('framework::app.approval-status')->with('formulir', $shares_sell->formulir);
        }
    }

    public function requestApproval()
    {
        access_is_allowed('approval.bumi.shares.sell');

        $view = view('bumi-shares::app.facility.bumi-shares.sell.request-approval');
        $view->list_shares_sell = Sell::joinFormulir()->notArchived()->approvalPending()->notCanceled()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('approval.bumi.shares.sell');

        if (count(app('request')->input('formulir_id')) == 0) {
            gritter_error('please select at least one form to request an approval');
            return redirect()->back();
        }

        $list_approver = Sell::joinFormulir()
            ->notArchived()
            ->whereIn('formulir_id', app('request')->input('formulir_id'))
            ->groupBy('formulir.approval_to')
            ->select('formulir.approval_to as approval_to')
            ->get();

        $request = $request->input();
        foreach ($list_approver as $data_approver) {
            $list_shares_sell = Sell::joinFormulir()
                ->notArchived()
                ->whereIn('formulir_id', app('request')->input('formulir_id'))
                ->where('approval_to', '=', $data_approver->approval_to)
                ->get();

            $token = md5(date('ymdhis'));
            $approver = User::find($data_approver->approval_to);
            $data = ['list_shares_sell' => $list_shares_sell, 'token' => $token, 'username' => auth()->user()->name, 'url' => url('/')];
            \Queue::push(function ($job) use ($request, $approver, $data) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('bumi-shares::emails.facility.bumi-shares.approval.sell', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval to sell stock #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_shares_sell as $shares_sell) {
                $shares_sell->formulir->request_approval_at = \Carbon::now();
                $shares_sell->formulir->request_approval_token = $token;
                $shares_sell->formulir->save();
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }
}

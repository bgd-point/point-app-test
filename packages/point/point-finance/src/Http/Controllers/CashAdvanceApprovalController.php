<?php

namespace Point\PointFinance\Http\Controllers;

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
use Point\PointFinance\Models\CashAdvance;

class CashAdvanceApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.finance.cash.advance');

        $view = view('point-finance::app.finance.point.cash-advance.request-approval');
        $view->list_cash_advance = CashAdvance::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.finance.cash.advance');
        $list_approver = CashAdvance::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_cash_advance = CashAdvance::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_cash_advance as $cash_advance) {
                array_push($array_formulir_id, $cash_advance->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_cash_advance,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id

            ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-finance::emails.finance.point.approval.cash-advance', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval cash advance #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_cash_advance as $cash_advance) {
                formulir_update_token($cash_advance->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $cash_advance = CashAdvance::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($cash_advance->formulir, $approval_message, 'approval.point.finance.cash.advance', $token);

        $cash_advance->is_payed = true;
        $cash_advance->save();

        $cash_advance->formulir->form_status = 1;
        $cash_advance->formulir->save();

        timeline_publish('approve', $cash_advance->formulir->form_number . ' approved', $this->getUserForTimeline($request, $cash_advance->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $cash_advance->formulir);
    }

    public function reject(Request $request, $id)
    {
        $cash_advance = CashAdvance::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($cash_advance->formulir, $approval_message, 'approval.point.finance.cash.advance', $token);
        timeline_publish('reject', 'cash advance ' . $cash_advance->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $cash_advance->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $cash_advance->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $cash_advance = CashAdvance::where('formulir_id', $id)->first();
            FormulirHelper::approve($cash_advance->formulir, $approval_message, 'approval.point.finance.cash.advance', $token);

            $cash_advance->is_payed = true;
            $cash_advance->save();

            $cash_advance->formulir->form_status = 1;
            $cash_advance->formulir->save();

            timeline_publish('approve', $cash_advance->formulir->form_number . ' approved', $cash_advance->formulir->approval_to);
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
            $cash_advance = CashAdvance::where('formulir_id', $id)->first();
            FormulirHelper::reject($cash_advance->formulir, $approval_message, 'approval.point.finance.cash.advance', $token);
            timeline_publish('reject', $cash_advance->formulir->form_number . ' rejected', $cash_advance->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

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
use Point\PointPurchasing\Models\Inventory\CashAdvance;

class CashAdvanceApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.cash.advance');

        $view = view('point-purchasing::app.purchasing.point.inventory.cash-advance.request-approval');
        $view->list_cash_advance = CashAdvance::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.cash.advance');
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
                \Mail::send('point-purchasing::emails.purchasing.point.approval.cash-advance', $data, function ($message) use ($approver) {
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

        FormulirHelper::approve($cash_advance->formulir, $approval_message, 'approval.point.purchasing.cash.advance', $token);
        self::addPaymentReference($cash_advance);
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

        FormulirHelper::reject($cash_advance->formulir, $approval_message, 'approval.point.purchasing.cash.advance', $token);
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
            FormulirHelper::approve($cash_advance->formulir, $approval_message, 'approval.point.purchasing.cash.advance', $token);
            self::addPaymentReference($cash_advance);
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
            FormulirHelper::reject($cash_advance->formulir, $approval_message, 'approval.point.purchasing.cash.advance', $token);
            timeline_publish('reject', $cash_advance->formulir->form_number . ' rejected', $cash_advance->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }

    private static function addPaymentReference($cash_advance)
    {
        $payment_reference = new PaymentReference;
        $payment_reference->payment_reference_id = $cash_advance->formulir->id;
        $payment_reference->person_id = $cash_advance->employee_id;
        $payment_reference->payment_flow = 'out';
        $payment_reference->payment_type = $cash_advance->payment_type;
        $payment_reference->total = $cash_advance->amount;
        $payment_reference->save();

        $purchasing_cash_advance_account = JournalHelper::getAccount('point purchasing', 'advance to employees');
        $payment_reference_detail = new PaymentReferenceDetail;
        $payment_reference_detail->point_finance_payment_reference_id = $payment_reference->id;
        $payment_reference_detail->coa_id = $purchasing_cash_advance_account;
        $payment_reference_detail->notes_detail;
        $payment_reference_detail->amount = $cash_advance->amount;
        $payment_reference_detail->allocation_id = 1;
        $payment_reference_detail->notes_detail = $cash_advance->formulir->notes;
        $payment_reference_detail->form_reference_id = $cash_advance->formulir->id;
        $payment_reference_detail->subledger_id = $cash_advance->employee_id;
        $payment_reference_detail->subledger_type = get_class(new Person);
        $payment_reference_detail->save();
    }
}

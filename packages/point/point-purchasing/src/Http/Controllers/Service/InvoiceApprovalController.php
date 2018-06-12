<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;

use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointPurchasing\Models\Service\Invoice;

class InvoiceApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.purchasing.service.invoice');

        $view = view('point-purchasing::app.purchasing.point.service.invoice.request-approval');
        $view->list_invoice = Invoice::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval()
    {
        access_is_allowed('create.point.purchasing.service.invoice');

        $list_approver = Invoice::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_invoice = Invoice::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_invoice as $invoice) {
                array_push($array_formulir_id, $invoice->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_invoice' => $list_invoice,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id

            ];
            
            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-purchasing::emails.purchasing.point.approval.service-invoice', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval invoice #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_invoice as $invoice) {
                formulir_update_token($invoice->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve()
    {

    }

    public function reject()
    {

    }

    public function approveAll()
    {

    }

    public function rejectAll()
    {
        
    }
}
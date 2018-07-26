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

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.purchasing.service.invoice');
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('You have sent email for invoice approval');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_invoice_id, $requester="VESA")
    {
        $token = md5(date('ymdhis'));
        $list_approver = Invoice::selectApproverList($list_invoice_id);

        foreach ($list_approver as $data_approver) {
            $list_invoice = Invoice::selectApproverRequest($list_invoice_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_invoice as $invoice) {
                array_push($array_formulir_id, $invoice->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_invoice' => $list_invoice,
                'token' => $token,
                'requester' => $requester,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id

            ];
            sendEmail(Invoice::bladeEmail(), $data, $approver->email, 'Request Approval Invoice #' . date('ymdHi'));
            
            foreach ($list_invoice as $invoice) {
                formulir_update_token($invoice->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($invoice->formulir, $approval_message, 'approval.point.purchasing.service.invoice', $token);
        timeline_publish('approve', $invoice->formulir->form_number . ' approved', $this->getUserForTimeline($request, $invoice->formulir->approval_to));

        DB::commit();
     
        gritter_success('Form approved', false);
        return $this->getRedirectLink($request, $invoice->formulir);

    }

    public function reject(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($invoice->formulir, $approval_message, 'approval.point.purchasing.service.invoice', $token);
        timeline_publish('reject', 'invoice ' . $invoice->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $invoice->formulir->approval_to));

        DB::commit();

        gritter_success('Form rejected', false);
        return $this->getRedirectLink($request, $invoice->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $invoice = Invoice::where('formulir_id', $id)->first();
            FormulirHelper::approve($invoice->formulir, $approval_message, 'approval.point.purchasing.service.invoice', $token);
            timeline_publish('approve', $invoice->formulir->form_number . ' approved', $invoice->formulir->approval_to);
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
            $invoice = Invoice::where('formulir_id', $id)->first();
            FormulirHelper::reject($invoice->formulir, $approval_message, 'approval.point.purchasing.service.invoice', $token);
            timeline_publish('reject', $invoice->formulir->form_number . ' rejected', $invoice->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}
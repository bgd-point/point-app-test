<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\PointSales\Models\Sales\Invoice;

class InvoiceApprovalPrintController extends Controller
{
    use ValidationTrait;

    public function requestApproval($id)
    {
        $view = view('point-sales::app.sales.point.sales.invoice.request-approval-print');
        $view->invoice = Invoice::find($id);
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        self::sendingRequestApproval($request->input('id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect('sales/point/indirect/invoice/'.$invoice->id);
    }

    public static function sendingRequestApproval($invoice_id, $requester="VESA")
    {
        $invoice = Invoice::find($invoice_id);
        $approver = User::find($invoice->formulir->approval_to);
        $token = md5(date('ymdhis'));
        $data = [
            'invoice' => $invoice,
            'token' => $token,
            'requester' => $requester,
            'url' => url('/'),
            'approver' => $approver,

        ];
        sendEmail(Invoice::bladeEmail(), $data, $approver->email, 'Request Approval to Print Sales Invoice #' . date('ymdHi'));

        $invoice->request_approval_print_token = $token;
        $invoice->request_approval_print_at = date('Y-m-d H:i:s');
        $invoice->approval_print_to = $approver->id;
        $invoice->save();
    }

    public function approve(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $token = \Input::get('token');

        if (!auth()->user()->may('approval.point.sales.invoice.print')) {
            throw new PointException('UNAUTHORIZED USER');
        }

        if ($invoice->request_approval_print_token != $token) {
            throw new PointException('TOKEN EXPIRED');
        }

        \DB::beginTransaction();
        $invoice->approval_print_status = 1;
        $invoice->approval_print_at = date('Y-m-d H:i:s');
        $invoice->save();

        \DB::commit();

        gritter_success('request approval approved');
        return view('point-sales::app.sales.point.sales.invoice.approval-print-status')->with('invoice', $invoice);
    }

    public function reject(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $token = \Input::get('token');

        if (!auth()->user()->may('approval.point.sales.invoice.print')) {
            throw new PointException('UNAUTHORIZED USER');
        }
        
        if ($invoice->request_approval_print_token != $token) {
            throw new PointException('TOKEN EXPIRED');
        }

        \DB::beginTransaction();
        $invoice->approval_print_status = -1;
        $invoice->save();

        \DB::commit();

        gritter_success('request approval rejected');
        return view('point-sales::app.sales.point.sales.invoice.approval-print-status')->with('invoice', $invoice);
    }
}

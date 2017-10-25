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
        $invoice = Invoice::find($request->input('id'));
        $approver = User::find($request->input('approval_to'));
        $token = md5(date('ymdhis'));
        $data = [
            'invoice' => $invoice,
            'token' => $token,
            'username' => auth()->user()->name,
            'url' => url('/'),
            'approver' => $approver,

        ];
        $request = $request->input();
        \Queue::push(function ($job) use ($approver, $data, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.approval.sales-invoice-request-approval-print', $data, function ($message) use ($approver) {
                $message->to($approver->email)->subject('request approval to print sales invoice #' . date('ymdHi'));
            });
            $job->delete();
        });

        $invoice->request_approval_print_token = $token;
        $invoice->request_approval_print_at = date('Y-m-d H:i:s');
        $invoice->approval_print_to = $approver->id;
        $invoice->save();

        gritter_success('send approval success');
        return redirect('sales/point/indirect/invoice/'.$invoice->id);
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

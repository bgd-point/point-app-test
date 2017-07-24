<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointSales\Models\Sales\SalesQuotation;

class SalesQuotationMailController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function mailQuotation()
    {
        access_is_allowed('create.point.sales.quotation');

        $view = view('point-sales::app.sales.point.sales.sales-quotation.mail-quotation');
        $view->list_sales_quotation = SalesQuotation::joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->selectOriginal()
            ->paginate(100);

        return $view;
    }

    public function _sendMailQuotation(Request $request)
    {
        access_is_allowed('create.point.sales.quotation');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $sales_quotation = SalesQuotation::find($request->input('sales_quotation_id'));
        $customer_email = $sales_quotation->person->email;

        $data = [
            'sales_quotation' => $sales_quotation,
            'username' => auth()->user()->name,
            'url' => url('/'),
        ];

        $request = $request->input();
        
        \Queue::push(function ($job) use ($data, $customer_email, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-sales::app.emails.sales.point.sales-quotation.mail-quotation', $data, function ($message) use ($customer_email) {
                $message->to($customer_email)->subject('Sales Quotation Mail #' . date('ymdHi'));
            });

            $job->delete();
        });

        $sales_quotation->send_mail_at = \Carbon::now();
        $sales_quotation->save();
        
        $response = array('status' =>'success', 'message'=> 'send quotation success') ;

        return response()->json($response);
    }
}

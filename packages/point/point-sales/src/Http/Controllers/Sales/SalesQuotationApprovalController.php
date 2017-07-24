<?php

namespace Point\PointSales\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointSales\Models\Sales\SalesQuotation;

class SalesQuotationApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.sales.quotation');

        $view = view('point-sales::app.sales.point.sales.sales-quotation.request-approval');
        $view->list_sales_quotation = SalesQuotation::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.sales.quotation');

        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        $list_approver = SalesQuotation::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_sales_quotation = SalesQuotation::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_sales_quotation as $sales_quotation) {
                array_push($array_formulir_id, $sales_quotation->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_sales_quotation,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-sales::app.emails.sales.point.approval.sales-quotation', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval sales quotation #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_sales_quotation as $sales_quotation) {
                formulir_update_token($sales_quotation->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $sales_quotation = SalesQuotation::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($sales_quotation->formulir, $approval_message, 'approval.point.sales.quotation', $token);
        timeline_publish('approve', 'sales quotation ' . $sales_quotation->formulir->form_number . ' approved', $this->getUserForTimeline($request, $sales_quotation->formulir->approval_to));

        DB::commit();

        gritter_success('form approved');

        return $this->getRedirectLink($request, $sales_quotation->formulir);
    }

    public function reject(Request $request, $id)
    {
        $sales_quotation = SalesQuotation::find($id);
        $approval_message = $request->input('approval_message') ? : '';
        $token = $request->input('token');

        DB::beginTransaction();

        FormulirHelper::reject($sales_quotation->formulir, $approval_message, 'approval.point.sales.quotation', $token);
        timeline_publish('reject', 'sales quotation ' . $sales_quotation->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $sales_quotation->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected');

        return $this->getRedirectLink($request, $sales_quotation->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $sales_quotation = SalesQuotation::where('formulir_id', $id)->first();
            FormulirHelper::approve($sales_quotation->formulir, $approval_message, 'approval.point.sales.quotation', $token);
            timeline_publish('approve', $sales_quotation->formulir->form_number . ' approved', $sales_quotation->formulir->approval_to);
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
            $sales_quotation = SalesQuotation::where('formulir_id', $id)->first();
            FormulirHelper::reject($sales_quotation->formulir, $approval_message, 'approval.point.sales.quotation', $token);
            timeline_publish('reject', $sales_quotation->formulir->form_number . ' rejected', $sales_quotation->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

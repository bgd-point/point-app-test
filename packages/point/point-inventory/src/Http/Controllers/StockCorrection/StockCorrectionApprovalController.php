<?php

namespace Point\PointInventory\Http\Controllers\StockCorrection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointInventory\Vesa\StockCorrectionVesa;
use Point\PointInventory\Helpers\StockCorrectionHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\BasicProduction\Models\Material;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;

class StockCorrectionApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.inventory.stock.correction');

        $view = view('point-inventory::app.inventory.point.stock-correction.request-approval');
        $view->list_stock_correction = StockCorrection::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.inventory.stock.correction');
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_stock_correction_id, $requester, $domain=url('/'))
    {
        $list_approver = StockCorrection::selectApproverList($list_stock_correction_id);
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_stock_correction = StockCorrection::selectApproverRequest($list_stock_correction_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_stock_correction as $stock_correction) {
                array_push($array_formulir_id, $stock_correction->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_stock_correction,
                'token' => $token,
                'requester' => $requester,
                'url' => $domain,
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            sendEmail(StockCorrection::bladeEmail(), $data, $approver->email, 'Request Approval Stock Correction #' . date('ymdHi'));

            foreach ($list_stock_correction as $stock_correction) {
                formulir_update_token($stock_correction->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $stock_correction = StockCorrection::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($stock_correction->formulir, $approval_message, 'approval.point.inventory.stock.correction', $token);
        FormulirHelper::close($stock_correction->formulir_id);
        StockCorrectionHelper::approve($stock_correction);
        StockCorrectionHelper::updateJournal($stock_correction);
        timeline_publish('approve', $stock_correction->formulir->form_number.' Approved', $this->getUserForTimeline($request, $stock_correction->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $stock_correction->formulir);
    }

    public function reject(Request $request, $id)
    {
        $stock_correction = StockCorrection::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        \FormulirHelper::reject($stock_correction->formulir, $approval_message, 'approval.point.inventory.stock.correction', $token);
        timeline_publish('reject', $stock_correction->formulir->form_number.' Rejected', $this->getUserForTimeline($request, $stock_correction->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $stock_correction->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $stock_correction = StockCorrection::where('formulir_id', $id)->first();
            FormulirHelper::approve($stock_correction->formulir, $approval_message, 'approval.point.inventory.stock.correction', $token);
            FormulirHelper::close($stock_correction->formulir_id);
            StockCorrectionHelper::approve($stock_correction);
            StockCorrectionHelper::updateJournal($stock_correction);
            timeline_publish('approve', $stock_correction->formulir->form_number . ' approved', $stock_correction->formulir->approval_to);
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
            $stock_correction = StockCorrection::where('formulir_id', $id)->first();
            FormulirHelper::reject($stock_correction->formulir, $approval_message, 'approval.point.inventory.stock.correction', $token);
            timeline_publish('reject', $stock_correction->formulir->form_number . ' rejected', $stock_correction->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

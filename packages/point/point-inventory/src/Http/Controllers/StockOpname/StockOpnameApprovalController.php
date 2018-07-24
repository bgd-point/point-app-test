<?php

namespace Point\PointInventory\Http\Controllers\StockOpname;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\PointInventory\Vesa\StockCorrectionVesa;
use Point\PointInventory\Helpers\StockOpnameHelper;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\BasicProduction\Models\Material;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;

class StockOpnameApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.inventory.stock.opname');

        $view = view('point-inventory::app.inventory.point.stock-opname.request-approval');
        $view->listStockOpname = StockOpname::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.inventory.stock.opname');
        self::sendingRequestApproval(app('request')->input('formulir_id'), auth()->user()->name);

        gritter_success('send approval success');
        return redirect()->back();
    }

    public static function sendingRequestApproval($list_stock_opname_id, $requester="VESA")
    {
        $list_approver = StockOpname::selectApproverList($list_stock_opname_id);
        $token = md5(date('ymdhis'));

        foreach ($list_approver as $data_approver) {
            $list_stock_opname = StockOpname::selectApproverRequest($list_stock_opname_id, $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_stock_opname as $stock_opname) {
                array_push($array_formulir_id, $stock_opname->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_stock_opname,
                'token' => $token,
                'requester' => $requester,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
            ];

            sendEmail(StockOpname::bladeEmail(), $data, $approver->email, 'Request Approval Stock Opname #' . date('ymdHi'));

            foreach ($list_stock_opname as $stock_opname) {
                formulir_update_token($stock_opname->formulir, $token);
            }
        }
    }

    public function approve(Request $request, $id)
    {
        $stock_opname = StockOpname::find($id);
        $approval_message = app('request')->input('approval_message') ? : '';
        $token = app('request')->input('token');

        DB::beginTransaction();

        FormulirHelper::approve($stock_opname->formulir, $approval_message, 'approval.point.inventory.stock.opname', $token);
        FormulirHelper::close($stock_opname->formulir_id);
        StockOpnameHelper::approve($stock_opname);
        StockOpnameHelper::updateJournal($stock_opname);
        timeline_publish('approval.point.inventory.stock.opname', 'Approve Stock Opname "'  . $stock_opname->formulir->form_number .'"', $this->getUserForTimeline($request, $stock_opname->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', false);
        return $this->getRedirectLink($request, $stock_opname->formulir);
    }

    public function reject(Request $request, $id)
    {
        $stock_opname = StockOpname::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        \DB::beginTransaction();

        \FormulirHelper::reject($stock_opname->formulir, $approval_message, 'approval.point.inventory.stock.opname', $token);
        timeline_publish('reject', $stock_opname->formulir->form_number.' Rejected', $this->getUserForTimeline($request, $stock_opname->formulir->approval_to));

        \DB::commit();

        gritter_success('form rejected', false);
        return $this->getRedirectLink($request, $stock_opname->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $stock_opname = StockOpname::where('formulir_id', $id)->first();
            FormulirHelper::approve($stock_opname->formulir, $approval_message, 'approval.point.inventory.stock.opname', $token);
            FormulirHelper::close($stock_opname->formulir_id);
            StockOpnameHelper::approve($stock_opname);
            StockOpnameHelper::updateJournal($stock_opname);
            timeline_publish('approve', $stock_opname->formulir->form_number . ' approved', $stock_opname->formulir->approval_to);
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
            $stock_opname = StockOpname::where('formulir_id', $id)->first();
            FormulirHelper::reject($stock_opname->formulir, $approval_message, 'approval.point.inventory.stock.opname', $token);
            timeline_publish('reject', $stock_opname->formulir->form_number . ' rejected', $stock_opname->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

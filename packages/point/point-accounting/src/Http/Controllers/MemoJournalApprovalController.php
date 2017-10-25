<?php

namespace Point\PointAccounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\Core\Models\User;
use Point\Framework\Traits\RequestApprovalTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\PointAccounting\Models\MemoJournal;
use Point\PointAccounting\Helpers\MemoJournalHelper;

class MemoJournalApprovalController extends Controller
{
    use ValidationTrait, RequestApprovalTrait;

    public function requestApproval()
    {
        access_is_allowed('create.point.accounting.memo.journal');

        $view = view('point-accounting::app.accounting.point.memo-journal.request-approval');
        $view->list_memo_journal = MemoJournal::selectRequestApproval()->paginate(100);
        return $view;
    }

    public function sendRequestApproval(Request $request)
    {
        access_is_allowed('create.point.accounting.memo.journal');

        if ($this->isFormulirNull($request)) {
            return redirect()->back();
        }

        $list_approver = MemoJournal::selectApproverList(app('request')->input('formulir_id'));
        $request = $request->input();
        $token = md5(date('ymdhis'));
        foreach ($list_approver as $data_approver) {
            $list_memo_journal = MemoJournal::selectApproverRequest(app('request')->input('formulir_id'), $data_approver->approval_to);
            $array_formulir_id = [];
            foreach ($list_memo_journal as $memo_journal) {
                array_push($array_formulir_id, $memo_journal->formulir_id);
            }

            $array_formulir_id = implode(',', $array_formulir_id);
            $approver = User::find($data_approver->approval_to);
            $data = [
                'list_data' => $list_memo_journal,
                'token' => $token,
                'username' => auth()->user()->name,
                'url' => url('/'),
                'approver' => $approver,
                'array_formulir_id' => $array_formulir_id
                ];

            \Queue::push(function ($job) use ($approver, $data, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Mail::send('point-accounting::emails.accounting.point.approval.memo-journal', $data, function ($message) use ($approver) {
                    $message->to($approver->email)->subject('request approval Memo Journal #' . date('ymdHi'));
                });
                $job->delete();
            });

            foreach ($list_memo_journal as $memo_journal) {
                formulir_update_token($memo_journal->formulir, $token);
            }
        }

        gritter_success('send approval success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        $memo_journal = MemoJournal::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::approve($memo_journal->formulir, $approval_message, 'approval.point.accounting.memo.journal', $token);
        MemoJournalHelper::addToJournal($memo_journal);
        timeline_publish('approve', 'memo journal ' . $memo_journal->formulir->form_number . ' approved', $this->getUserForTimeline($request, $memo_journal->formulir->approval_to));

        DB::commit();

        gritter_success('form approved', 'false');
        return $this->getRedirectLink($request, $memo_journal->formulir);
    }

    public function reject(Request $request, $id)
    {
        $memo_journal = MemoJournal::find($id);
        $approval_message = \Input::get('approval_message') ? : '';
        $token = \Input::get('token');

        DB::beginTransaction();

        FormulirHelper::reject($memo_journal->formulir, $approval_message, 'approval.point.accounting.memo.journal', $token);
        timeline_publish('reject', 'sales quotation ' . $memo_journal->formulir->form_number . ' rejected', $this->getUserForTimeline($request, $memo_journal->formulir->approval_to));

        DB::commit();

        gritter_success('form rejected', 'false');
        return $this->getRedirectLink($request, $memo_journal->formulir);
    }

    public function approveAll()
    {
        $token = \Input::get('token');
        $array_formulir_id = explode(',', \Input::get('formulir_id'));
        $approval_message = '';

        DB::beginTransaction();
        foreach ($array_formulir_id as $id) {
            $memo_journal = MemoJournal::where('formulir_id', $id)->first();
            FormulirHelper::approve($memo_journal->formulir, $approval_message, 'approval.point.accounting.memo.journal', $token);
            timeline_publish('approve', $memo_journal->formulir->form_number . ' approved', $memo_journal->formulir->approval_to);
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
            $memo_journal = MemoJournal::where('formulir_id', $id)->first();
            FormulirHelper::reject($memo_journal->formulir, $approval_message, 'approval.point.accounting.memo.journal', $token);
            timeline_publish('reject', $memo_journal->formulir->form_number . ' rejected', $memo_journal->formulir->approval_to);
        }
        DB::commit();

        $view = view('framework::app.approval-all-status');
        $view->array_formulir_id = $array_formulir_id;
        $view->formulir = \Input::get('formulir_id');

        return $view;
    }
}

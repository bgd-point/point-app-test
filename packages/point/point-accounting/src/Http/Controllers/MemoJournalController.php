<?php

namespace Point\PointAccounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\PointAccounting\Helpers\MemoJournalHelper;
use Point\PointAccounting\Models\MemoJournal;
use Point\PointAccounting\Models\MemoJournalDetail;

class MemoJournalController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.accounting.memo.journal');
        $list_memo_journal = MemoJournal::joinDependencies();
        $list_memo_journal = MemoJournalHelper::searchList($list_memo_journal, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view = view('point-accounting::app.accounting.point.memo-journal.index');
        $view->list_memo_journal = $list_memo_journal->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.accounting.memo.journal');

        $list_coa = Coa::all();
        $view = view('point-accounting::app.accounting.point.memo-journal.create');
        $view->list_coa = $list_coa;
        $view->list_user_approval = UserHelper::getAllUser();
        $view->details = TempDataHelper::get('memo.journal', auth()->user()->id);
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        self::storeTemp($request);
        $this->validate($request, [
            'coa_id'=>'required',
            'debit'=>'required',
            'credit'=>'required',
            'foot_debit'=>'required',
            'foot_credit'=>'required',
            'form_date'=>'required',
            'approval_to'=>'required'
        ]);

        formulir_is_allowed_to_create('create.point.accounting.memo.journal', date_format_db($request->input('form_date')), []);
        if (!self::validation($request)) {
            gritter_error('Failed, Please check your input. Debit and credit must balance and coa is required');
            return redirect()->back();
        }

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-accounting-memo-journal');
        $memo_journal = MemoJournalHelper::create($formulir->id, $request);
        TempDataHelper::clear('memo.journal', auth()->user()->id);
        timeline_publish('create.memo.journal', 'create memo journal ' . $memo_journal->formulir->form_number . ' success');

        DB::commit();

        gritter_success('Memo Journal has been saved', 'false');
        return redirect('accounting/point/memo-journal/'.$memo_journal->id);
    }

    public function validation($request)
    {
        $coa_id = $request->input('coa_id');
        for ($i=0; $i<count($coa_id); $i++) {
            if ($coa_id[$i] == '') {
                return false;
            }

            $coa = Coa::find($coa_id[$i]);
            if ($coa->has_subledger && $request->input('master')[$i] == '') {
                return false;
            }
        }
        if ($request->input('foot_debit') == 0 || $request->input('foot_credit') == 0) {
            return false;
        }
        if (number_format_db($request->input('foot_debit')) != number_format_db($request->input('foot_credit'))) {
            return false;
        }

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.accounting.memo.journal');

        $view = view('point-accounting::app.accounting.point.memo-journal.show');
        $view->memo_journal = MemoJournal::find($id);
        $view->list_memo_journal_archived = MemoJournal::joinFormulir()->archived($view->memo_journal->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_memo_journal_archived->count();
        return $view;
    }

    /**
     * Display the specified archived resource.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function archived($id)
    {
        access_is_allowed('read.point.accounting.memo.journal');

        $view = view('point-accounting::app.accounting.point.memo-journal.archived');
        $view->memo_journal_archived = MemoJournal::find($id);
        $view->memo_journal = MemoJournal::joinFormulir()->notArchived($view->memo_journal_archived->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.point.accounting.memo.journal');
        $memo_journal = MemoJournal::find($id);
        
        if (!$memo_journal && !$memo_journal->formulir->form_number==null) {
            gritter_error('Memo Journal not found', 'false');
            return redirect('accounting/point/memo-journal');
        }
        
        $list_coa = Coa::all();
        $view = view('point-accounting::app.accounting.point.memo-journal.edit');
        $view->list_coa = $list_coa;
        $view->memo_journal = $memo_journal;

        $memo_journal_detail = MemoJournalDetail::where('memo_journal_id', $id)->get();
        self::storeTempEdit($memo_journal_detail);
        $view->details = TempDataHelper::get('memo.journal', auth()->user()->id);
        $view->list_user_approval = UserHelper::getAllUser();

        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'coa_id'=>'required',
            'debit'=>'required',
            'credit'=>'required',
            'foot_debit'=>'required',
            'foot_credit'=>'required',
            'form_date' => 'required',
            'edit_notes' => 'required',
            'approval_to' => 'required'
        ]);

        $memo_journal = MemoJournal::find($id);
        formulir_is_allowed_to_update('update.point.accounting.memo.journal', date_format_db($request->input('form_date')), $memo_journal->formulir);
        if (!self::validation($request)) {
            gritter_error('Failed, Please check your input. Debit and credit must balance and coa is required');
            return redirect()->back();
        }

        DB::beginTransaction();

        $formulir_old = FormulirHelper::archive($request->input(), $memo_journal->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $memo_journal = MemoJournalHelper::create($formulir->id, $request);
        timeline_publish('update.memo.journal', 'update memo journal ' . $memo_journal->formulir->form_number . ' success');
        TempDataHelper::clear('memo.journal', auth()->user()->id);

        DB::commit();

        gritter_success('Memo Journal has been saved');
        return redirect('accounting/point/memo-journal/'.$memo_journal->id);
    }

    public function _masterReference()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $coa_id = \Input::get('id');
        \Log::info('coa: '.$coa_id);
        try {
            $list_journal = Journal::joinCoa()->coaHasSubleger()->where('coa.id', $coa_id)->get();

            $result = [];

        if ($list_journal) {
            foreach ($list_journal as $journal) {
                if ($journal->subledger_id && $journal->subledger_type) {
                    $subledger = $journal->subledger_type::find($journal->subledger_id);
                    $temp = array(
                        'value' => $journal->subledger_id.'#'.$journal->subledger_type,
                        'text'  => $subledger->name
                    );
                    array_push($result, $temp);
                }
            }
        }

        $response = array(
            'lists' => $result,
        );

        return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Error in joinCoa query: '.$e->getMessage());
            \Log::error($e->getTraceAsString());
        }
        
    }

    public function _formReference()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $master_id = explode('#', \Input::get('master_id'));
        $master_id = $master_id[0];

        $coa_id = \Input::get('coa_id');
        $list_journal = Journal::where('coa_id', $coa_id)
            ->where('subledger_id', $master_id)
            ->get();
        $result = [];

        if ($list_journal) {
            foreach ($list_journal as $journal) {
                if ($journal->subledger_id && $journal->subledger_type) {
                    $formulir = Formulir::find($journal->form_journal_id);
                    $temp = array(
                        'value' => $formulir->id,
                        'text'  => $formulir->form_number.' #'.$formulir->notes
                    );
                    array_push($result, $temp);
                }
            }
        }

        $response = array(
            'lists' => $result,
        );

        return response()->json($response);
    }

    public function storeTemp($request)
    {
        TempDataHelper::clear('memo.journal', auth()->user()->id);

        for ($i=0; $i < count($request->input('coa_id')); $i++) {
            $subledger = $request->input('master')[$i];
            $master_id = explode('#', $subledger);
            $subledger_id = '';
            $subledger_type = '';

            if ($subledger) {
                $subledger_id = $master_id[0];
                $subledger_type = $master_id[1];
            }

            $temp = new Temp;
            $temp->user_id = auth()->user()->id;
            $temp->name = 'memo.journal';
            $temp->keys = serialize([
                'coa_id'=>$request->input('coa_id')[$i],
                'subledger_id'=>$subledger_id,
                'subledger_type'=>$subledger_type,
                'form_reference_id'=>$request->input('invoice')[$i],
                'description'=>$request->input('desc')[$i],
                'debit'=>number_format_db($request->input('debit')[$i]),
                'credit'=>number_format_db($request->input('credit')[$i]),
            ]);
            $temp->save();
        }

        return true;
    }

    public function storeTempEdit($memo_journal_detail)
    {
        TempDataHelper::clear('memo.journal', auth()->user()->id);

        foreach ($memo_journal_detail as $detail) {
            $temp = new Temp;
            $temp->user_id = auth()->user()->id;
            $temp->name = 'memo.journal';
            $temp->keys = serialize([
                'coa_id'=>$detail->coa_id,
                'subledger_id'=>$detail->subledger_id,
                'subledger_type'=>$detail->subledger_type,
                'form_reference_id'=>$detail->form_reference_id,
                'description'=>$detail->description,
                'debit'=>$detail->debit,
                'credit'=>$detail->credit,
            ]);
            $temp->save();
        }
        
        return true;
    }

    public function _removeTemp()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $rowid = \Input::get('rowid');
        TempDataHelper::remove($rowid);
        
        print $rowid;
    }

    public function clear()
    {
        TempDataHelper::clear('memo.journal', auth()->user()->id);
        
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function cancel($id)
    {
        TempDataHelper::clear('memo.journal', auth()->user()->id);
        return redirect('accounting/point/memo-journal/'.$id);
    }
}

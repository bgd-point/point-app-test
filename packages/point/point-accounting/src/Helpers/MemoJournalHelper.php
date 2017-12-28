<?php

namespace Point\PointAccounting\Helpers;

use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\PointAccounting\Models\MemoJournal;
use Point\PointAccounting\Models\MemoJournalDetail;
use Point\PointFinance\Models\PaymentOrder\PaymentOrderDetail;

class MemoJournalHelper
{
    public static function searchList($list_memo_journal, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_memo_journal = $list_memo_journal->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($order_by) {
            $list_memo_journal = $list_memo_journal->orderBy($order_by, $order_type);
        } else {
            $list_memo_journal = $list_memo_journal->orderByStandard();
        }

        if ($date_from) {
            $list_memo_journal = $list_memo_journal->where('formulir.form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_memo_journal = $list_memo_journal->where('formulir.form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            $list_memo_journal = $list_memo_journal->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('formulir.notes', 'like', '%'.$search.'%');
            });
        }

        return $list_memo_journal;
    }

    public static function create($formulir_id, $request)
    {
        $memo_journal = new MemoJournal;
        $memo_journal->formulir_id = $formulir_id;
        $memo_journal->debit = number_format_db($request->input('foot_debit'));
        $memo_journal->credit = number_format_db($request->input('foot_credit'));
        $memo_journal->save();

        $items = [
            'coa_id'=>$request->input('coa_id'),
            'subledger'=>$request->input('master'),
            'form_journal_id'=>$formulir_id,
            'form_reference_id'=>$request->input('invoice'),
            'description'=>$request->input('desc'),
            'debit'=>$request->input('debit'),
            'credit'=>$request->input('credit'),
        ];

        self::addDetails($memo_journal, $items);
        return $memo_journal;
    }

    public static function addDetails($memo_journal, $items)
    {
        extract($items);
        for ($i=0; $i<count($coa_id); $i++) {
            $memo_journal_detail = new MemoJournalDetail;
            $memo_journal_detail->memo_journal_id = $memo_journal->id;
            $memo_journal_detail->coa_id = $coa_id[$i];

            $master_id = explode('#', $subledger[$i]);
            $subledger_id = '';
            $subledger_type = '';

            if ($subledger[$i]) {
                $subledger_id = $master_id[0];
                $subledger_type = $master_id[1];
            }

            $memo_journal_detail->description = $description[$i];
            $memo_journal_detail->debit = 0;
            $memo_journal_detail->credit = 0;

            $memo_journal_detail->form_journal_id = $memo_journal->formulir_id;
            $memo_journal_detail->form_reference_id = $memo_journal_detail->coa->has_subledger ? $form_reference_id[$i] : null;
            if ($debit[$i]) {
                $memo_journal_detail->debit = number_format_db($debit[$i]);
            }

            if ($credit[$i]) {
                $memo_journal_detail->credit = number_format_db($credit[$i]);
            }

            $ref = Formulir::find($memo_journal_detail->form_reference_id);

            if ($subledger[$i]) {
                $memo_journal_detail->subledger_id = $subledger_id;
                $memo_journal_detail->subledger_type = $subledger_type;
                ReferHelper::create(
                    $ref->formulirable_type,
                    $ref->formulirable_id,
                    get_class($memo_journal_detail),
                    $memo_journal_detail->id,
                    get_class($memo_journal),
                    $memo_journal->id,
                    $memo_journal_detail->debit ?: $memo_journal_detail->credit
                );
            }

            $memo_journal_detail->save();

//            $close_status = ReferHelper::closeStatus(
//                $subledger_type,
//                $subledger_id,
//                $references_amount_original[$i],
//                0
//            );
//
//            formulir_lock($reference->formulir_id, $payment_order->formulir_id);

//            if ($close_status) {
//                if (get_class($reference) != get_class(new CutOffPayableDetail())) {
//                    $reference->formulir->form_status = 1;
//                    $reference->formulir->save();
//                }
//            }
        }
    }

    public static function isAjeBalance($debit, $credit)
    {
        $debit = number_format_db($debit);
        $credit = number_format_db($credit);
        if ($debit == $credit) {
            return true;
        } else {
            return false;
        }
    }

    public static function addToJournal($memo_journal)
    {
        foreach ($memo_journal->detail as $memo_journal_item) {
            $journal = new Journal;
            $journal->form_date = $memo_journal->formulir->form_date;
            $journal->coa_id = $memo_journal_item->coa_id;
            $journal->description = 'Memo Journal with '.$memo_journal->formulir->form_number;
            $journal->debit = $memo_journal_item->debit;
            $journal->credit = $memo_journal_item->credit;
            $journal->form_journal_id = $memo_journal->formulir_id;
            $journal->form_reference_id = $memo_journal_item->form_reference_id ?: null;
            $journal->subledger_id = $memo_journal_item->subledger_id ?: null;
            $journal->subledger_type = $memo_journal_item->subledger_type ?: null;

            $journal->save();
        }
        
        JournalHelper::checkJournalBalance($memo_journal->formulir_id);
        return $memo_journal;
    }
}

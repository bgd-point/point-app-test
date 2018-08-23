<?php

namespace Point\PointAccounting\Vesa;

use Point\PointAccounting\Models\MemoJournal;

trait MemoJournalVesa
{
    public static function getVesa()
    {
        $array = self::vesaApproval();
        $array = self::vesaReject($array);
        return $array;
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaReject()
    {
        return self::vesaReject([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_memo_journal = self::joinFormulir()->notArchived()->open()->approvalPending()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_memo_journal->count() > 5) {
            array_push($array, [
                'url' => url('accounting/point/memo-journal/vesa-approval'),
                'deadline' => $list_memo_journal->orderBy('form_date')->first()->form_date,
                'message' => 'please approve accounting order',
                'permission_slug' => 'approval.point.accounting.memo.journal'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_memo_journal->get() as $memo_journal) {
            array_push($array, [
                'url' => url('accounting/point/memo-journal/' . $memo_journal->id),
                'deadline' => $memo_journal->required_date ? : $memo_journal->formulir->form_date,
                'message' => 'please approve this memo journal ' . $memo_journal->formulir->form_number,
                'permission_slug' => 'approval.point.accounting.memo.journal'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_memo_journal = self::joinFormulir()->notArchived()->open()->approvalRejected()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_memo_journal->count() > 5) {
            array_push($array, [
                'url' => url('accounting/point/memo-journal/vesa-rejected'),
                'deadline' => $list_memo_journal->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.accounting.memo.journal'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_memo_journal->get() as $memo_journal) {
            array_push($array, [
                'url' => url('accounting/point/memo-journal/' . $memo_journal->id.'/edit'),
                'deadline' => $memo_journal->required_date ? : $memo_journal->formulir->form_date,
                'message' => $memo_journal->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.accounting.memo.journal'
            ]);
        }

        return $array;
    }
}

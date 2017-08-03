<?php

namespace Point\Framework\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;

class JournalHelper
{
    public static function position($coa_id)
    {
        $coa = Coa::find($coa_id);

        if (!$coa) {
            throw new PointException('You receive this error because your account not connected to this feature');
        }

        if ($coa->category->position->debit) {
            return 'debit';
        }

        return 'credit';
    }

    public static function checkSetup($group)
    {
        $account = SettingJournal::where('group', '=', $group)->whereNull('coa_id')->first();

        if ($account) {
            return false;
        }

        return true;
    }

    public static function getAccount($group, $account_name)
    {
        $account = SettingJournal::where('group', '=', $group)->where('name', $account_name)->first();

        if (!$account) {
            throw new PointException('Contact administrator to setup account journal');
        }

        return $account->coa_id;
    }

    public static function remove($form_journal_id)
    {
        Journal::where('form_journal_id', '=', $form_journal_id)->delete();
    }

    public static function getTotalValue($coa_id, $date)
    {
        $journal = Journal::where('coa_id', '=', $coa_id)
            ->where('form_date', '<=', $date)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->first();

        if (!$journal || $journal->coa_id == null) {
            $journal = new \stdClass();
            $journal->debit = 0;
            $journal->credit = 0;
            $journal->coa_id = $coa_id;
        }

        return $journal;
    }

    public static function getTotalValueBySubledger($coa_id, $date, $subledger_type, $subledger_id)
    {
        $journal = Journal::where('coa_id', '=', $coa_id)
            ->where('form_date', '<=', $date)
            ->where('subledger_type', '=', $subledger_type)
            ->where('subledger_id', '=', $subledger_id)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->first();

        if (!$journal || $journal->coa_id == null) {
            $journal = new \stdClass();
            $journal->debit = 0;
            $journal->credit = 0;
            $journal->coa_id = $coa_id;
        }

        return $journal;
    }

    public static function coaValue($coa_id, $date_from = null, $date_to)
    {
        $journal = Journal::where('coa_id', '=', $coa_id)
            ->where('form_date', '<=', $date_to)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id');

        if ($date_from) {
            $journal = $journal->where('form_date', '>=', $date_from);
        }

        $journal = $journal->first();

        if (!$journal || $journal->coa_id == null) {
            return 0;
        }

        return static::journalValue($journal);
    }

    public static function journalValue($journal)
    {
        if ($journal->coa->category->position->debit) {
            return $journal->debit - $journal->credit;
        } else {
            return $journal->credit - $journal->debit;
        }
    }

    public static function groupValue($coa_group_id, $date_from, $date_to)
    {
        $coa_from_group = CoaGroup::where('coa_id', '=', $coa_group_id)->list('coa.id');
        $journal = Journal::whereIn('coa_id', $coa_from_group)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->first();

        if (!$journal || $journal->coa_id == null) {
            return 0;
        }

        return static::journalValue($journal);
    }

    public static function categoryValue($coa_category_id, $date_from, $date_to)
    {
        $coa_from_category = Coa::where('coa_category_id', '=', $coa_category_id)->lists('coa.id');

        $journal_open = Journal::whereIn('coa_id', $coa_from_category)
            ->where('form_date', '<', $date_from)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->first();

        $journal = Journal::whereIn('coa_id', $coa_from_category)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->first();

        if (!$journal || $journal->coa_id == null || !$journal_open || $journal_open->coa_id == null) {
            return 0;
        }

        return static::journalValue($journal_open) + static::journalValue($journal);
    }

    public static function positionValue($coa_position_id, $date_from, $date_to)
    {
        $coa_from_position = Coa::join('coa_category', 'coa_category.id', '=', 'coa.coa_category_id')
            ->join('coa_position', 'coa_position.id', '=', 'coa_category.coa_position_id')
            ->where('coa_position_id', '=', $coa_position_id)
            ->lists('coa.id');

        $journal = Journal::whereIn('coa_id', $coa_from_position)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->first();

        if (!$journal || $journal->coa_id == null) {
            return 0;
        }

        return static::journalValue($journal);
    }

    public static function coaOpeningBalance($coa_id, $date_from)
    {
        $journal = Journal::where('coa_id', $coa_id)
            ->where('form_date', '<', $date_from)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->groupBy('coa_id')
            ->first();

        if (!$journal) {
            return 0;
        }

        return static::journalValue($journal);
    }

    public static function coaEndingBalance($coa_id, $date_to)
    {
        $journal = Journal::where('coa_id', $coa_id)
            ->where('form_date', '<=', $date_to)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->groupBy('coa_id')
            ->first();

        if (!$journal) {
            return 0;
        }

        return static::journalValue($journal);
    }

    public function groupCategoryValue($coa_group_category_id, $date_from, $date_to)
    {
        $coa_from_group_category = Coa::join('coa_category', 'coa_category.id', '=', 'coa.coa_category_id')
            ->join('coa_group_category', 'coa_category.coa_group_category_id', '=', 'coa_group_category.id')
            ->where('coa_group_category_id', '=', $coa_group_category_id)
            ->lists('coa.id');

        $journal = Journal::whereIn('coa_id', $coa_from_group_category)
            ->where('form_date', '>=', $date_from)
            ->where('form_date', '<=', $date_to)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->first();

        if (!$journal || $journal->coa_id == null) {
            return 0;
        }

        return static::journalValue($journal);
    }

    public static function coaDebit($coa_id, $date_from, $date_to)
    {
        $journal_debit = Journal::where('coa_id', $coa_id)
            ->whereBetween('form_date', [$date_from, $date_to])
            ->sum('debit');
        return $journal_debit;
    }

    public static function coaCredit($coa_id, $date_from, $date_to)
    {
        $journal_debit = Journal::where('coa_id', $coa_id)
            ->whereBetween('form_date', [$date_from, $date_to])
            ->sum('credit');
        return $journal_debit;
    }
}

<?php

namespace Point\Framework\Helpers;

use Point\Framework\Models\Journal;

class AccountingHelper
{
    public static function querySubledger($date_from, $date_to, $subledger_id, $coa_id)
    {
        if ($coa_id < 1) {
            return [];
        }

        $q = Journal::where('coa_id', '=', $coa_id)
                ->where('form_date', '>=', $date_from)
                ->where('form_date', '<=', $date_to)
                ->where(function ($query) use ($subledger_id) {
                    if ($subledger_id != 'all') {
                        $query->where('subledger_id', $subledger_id);
                    } elseif ($subledger_id == 'all') {
                        $query->groupBy('subledger_id');
                    } else {
                        $query->groupBy('subledger_id');
                    }
                })
                ->orderBy('form_date');
                

        if ((request()->get('database_name') == 'p_test' || request()->get('database_name') == 'p_personalfinance') && auth()->user()->name != 'lioni') {
            $q = $q->join('coa', 'coa.id', '=', 'journal.coa_id')->where('coa.name', 'not like', '%lioni%');
        }

        return $q->get();
    }

    public static function queryGeneralLedger($date_from, $date_to, $coa_id)
    {
        if ($coa_id < 1) {
            return null;
        } else {
            $q = Journal::join('formulir','formulir.id', '=', 'journal.form_journal_id')
                ->where('journal.form_date', '>=', $date_from)
                ->where('journal.form_date', '<=', $date_to)
                ->where('formulir.form_status', '!=', -1)
                ->whereIn('coa_id', $coa_id)
                ->orderBy('journal.form_date');

            if ((request()->get('database_name') == 'p_test' || request()->get('database_name') == 'p_personalfinance') && auth()->user()->name != 'lioni') {
                $q = $q->join('coa', 'coa.id', '=', 'journal.coa_id')->where('coa.name', 'not like', '%lioni%');
            }

            return $q->get();
        }
    }

    public static function queryGeneralLedgerAll($date_from, $date_to, $coa_id)
    {
        if ($coa_id < 1) {
            return null;
        } else {
            $journals = Journal::join('formulir','formulir.id', '=', 'journal.form_journal_id')
                ->where('journal.form_date', '>=', $date_from)
                ->where('journal.form_date', '<=', $date_to)
                ->where('coa_id', $coa_id)
                ->where('formulir.form_status', '!=', -1)
                ->orderBy('journal.form_date');

            if ((request()->get('database_name') == 'p_test' || request()->get('database_name') == 'p_personalfinance') && auth()->user()->name != 'lioni') {
                $journals = $journals->join('coa', 'coa.id', '=', 'journal.coa_id')->where('coa.name', 'not like', '%lioni%');
            }

            if ($journals->count()) {
                return $journals->get();
            } else {
                return null;
            }
        }
    }

    public static function coaOpeningBalance($coa_id, $date_from)
    {
        $journal = Journal::where('form_date', '<', $date_from);
        if (is_array($coa_id)) {
            $journal = $journal->whereIn('coa_id', $coa_id);
        } else {
            $journal = $journal->where('coa_id', $coa_id);
        }

        $journal = $journal
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->groupBy('coa_id')
            ->first();


        if (!$journal) {
            return 0;
        }

        if ($journal->coa->category->position->debit == true) {
            return $journal->debit - $journal->credit;
        }

        return $journal->credit - $journal->debit;
    }

    public static function coaOpeningBalanceAll($coa_id, $date_from)
    {
        $journal = Journal::where('coa_id', $coa_id)
            ->where('form_date', '<', $date_from)
            ->selectRaw('sum(debit) as debit, sum(credit) as credit, coa_id')
            ->groupBy('coa_id')
            ->first();

        if (!$journal) {
            return 0;
        }

        if ($journal->coa->category->position->debit == true) {
            return $journal->debit - $journal->credit;
        }

        return $journal->credit - $journal->debit;
    }
}

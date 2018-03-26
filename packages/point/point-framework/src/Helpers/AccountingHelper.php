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

        return Journal::where('coa_id', '=', $coa_id)
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
                ->orderBy('form_date')
                ->get();
    }

    public static function queryGeneralLedger($date_from, $date_to, $coa_id)
    {
        if ($coa_id < 1) {
            return null;
        } else {
            return Journal::where('form_date', '>=', $date_from)
                ->where('form_date', '<=', $date_to)
                ->whereIn('coa_id', $coa_id)
                ->orderBy('form_date')
                ->get();
        }
    }

    public static function queryGeneralLedgerAll($date_from, $date_to, $coa_id)
    {
        if ($coa_id < 1) {
            return null;
        } else {
            $journals = Journal::where('form_date', '>=', $date_from)
                ->where('form_date', '<=', $date_to)
                ->where('coa_id', $coa_id)
                ->orderBy('form_date')
                ->get();

            if ($journals->count()) {
                return $journals;
            } else {
                return null;
            }
        }
    }

    public static function coaOpeningBalance($coa_id, $date_from)
    {
        $journal = Journal::whereIn('coa_id', $coa_id)
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

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
	            ->where(function($query) use ($subledger_id) {
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
			return [];
		}

		return Journal::where('coa_id', '=', $coa_id)
                ->where('form_date', '>=', $date_from)
                ->where('form_date', '<=', $date_to)
                ->orderBy('form_date')
                ->get();
	}
}
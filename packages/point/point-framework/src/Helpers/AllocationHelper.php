<?php

namespace Point\Framework\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Framework\Models\Master\AllocationReport;

class AllocationHelper
{
	public static function save($formulir_id, $allocation_id, $amount)
	{
		$allocation = new AllocationReport();
		$allocation->formulir_id = $formulir_id;
		$allocation->allocation_id = $allocation_id;
		$allocation->amount = $amount;
		$allocation->save();		
	}

    public static function remove($formulir_id)
    {
        $allocation = AllocationReport::where('formulir_id', $formulir_id);
        if (!$allocation) {
            throw new PointException("FORMULIR NOT FOUND");
        }
        
        $allocation->delete();
    }

	public static function searchList($date_from, $date_to, $search, $allocation_id, $groupBy)
    {
    	$list_allocation_report = AllocationReport::joinFormulir()->joinAllocation()->notArchived();
        if ($date_from) {
            $list_allocation_report = $list_allocation_report->where('formulir.form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_allocation_report = $list_allocation_report->where('formulir.form_date', '<=', date_format_db($date_to, 'end'));
        }
        
        if ($search) {
            $list_allocation_report = $list_allocation_report->where('allocation.name', 'like', '%' . $search . '%');
        }

        if ($allocation_id) {
            $list_allocation_report = $list_allocation_report->where('allocation.id', $allocation_id);
        }

        if ($groupBy) {
            $list_allocation_report = $list_allocation_report->groupBy('allocation_id');
            $list_allocation_report = $list_allocation_report->selectRaw('sum(amount) as amount, allocation_report.id, allocation_report.formulir_id, allocation_report.allocation_id'); 
        } else {
            $list_allocation_report = $list_allocation_report->select('allocation_report.*'); 
        }


        
        return $list_allocation_report;
    }
}
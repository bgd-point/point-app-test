<?php

namespace Point\PointAccounting\Helpers;

use Point\Core\Helpers\TempDataHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReceivableHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Receivable;
use Point\PointAccounting\Models\CutOff;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffAccountSubledger;
use Point\PointAccounting\Models\CutOffReceivable;
use Point\PointAccounting\Models\CutOffReceivableDetail;

class CutOffReceivableHelper {
	
    public static function searchList($list_cut_off, $date_from, $date_to, $search){

        if($date_from)
            $list_cut_off = $list_cut_off->where('form_date','>=',date_format_db($date_from, 'start'));

        if($date_to)
            $list_cut_off = $list_cut_off->where('form_date','<=',date_format_db($date_to, 'end'));

        if($search) {
            // search input to database
            $list_cut_off = $list_cut_off->where(function($q) use($search) {
                $q->where('person.name','like','%'.$search.'%')
                  ->orWhere('formulir.form_number','like','%'.$search.'%');
            });
        }

        return $list_cut_off;
    }

    public static function create($formulir) {
        $cut_off_receivable = new CutOffReceivable;
        $cut_off_receivable->formulir_id = $formulir->id;
        $cut_off_receivable->save();

        $details = TempDataHelper::get('cut.off.receivable', auth()->user()->id);
        $coa_temp = [];
        foreach ($details as $receivable) {
            $cut_off_receivable_detail = new CutOffReceivableDetail;
            $cut_off_receivable_detail->cut_off_receivable_id = $cut_off_receivable->id;
            $cut_off_receivable_detail->coa_id = $receivable['coa_id'];
            $cut_off_receivable_detail->subledger_id = $receivable['subledger_id'];
            $cut_off_receivable_detail->subledger_type =$receivable['type'];
            $cut_off_receivable_detail->amount = number_format_db($receivable['amount']);
            $cut_off_receivable_detail->notes = $receivable['notes'];

            $cut_off_receivable_detail->save();
            array_push($coa_temp, $receivable['coa_id']);
        }

        $coa = \Input::get('coa_id');
        for ($i=0; $i < count($coa); $i++) { 
            if (in_array($coa[$i], $coa_temp)) {
                continue;
            }

            if (\Input::get('amount')[$i]) {
                $cut_off_receivable_detail = new CutOffReceivableDetail;
                $cut_off_receivable_detail->cut_off_receivable_id = $cut_off_receivable->id;
                $cut_off_receivable_detail->coa_id = $coa[$i];
                $cut_off_receivable_detail->subledger_id = 0;
                $cut_off_receivable_detail->subledger_type = 0;
                $cut_off_receivable_detail->amount = number_format_db(\Input::get('amount')[$i]);
                $cut_off_receivable_detail->notes = '';

                $cut_off_receivable_detail->save();    
            }
        }
        
		return $cut_off_receivable;
    }
}

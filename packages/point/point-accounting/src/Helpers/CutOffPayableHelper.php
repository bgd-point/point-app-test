<?php

namespace Point\PointAccounting\Helpers;

use Point\Core\Helpers\TempDataHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\PayableHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Payable;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffAccountSubledger;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffPayableDetail;

class CutOffPayableHelper {
	
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
        $cut_off_payable = new CutOffPayable;
        $cut_off_payable->formulir_id = $formulir->id;
        $cut_off_payable->save();

        $details = TempDataHelper::get('cut.off.payable', auth()->user()->id);
        $coa_temp = [];
        foreach ($details as $payable) {
            $cut_off_payable_detail = new CutOffPayableDetail;
            $cut_off_payable_detail->cut_off_payable_id = $cut_off_payable->id;
            $cut_off_payable_detail->coa_id = $payable['coa_id'];
            $cut_off_payable_detail->subledger_id = $payable['subledger_id'];
            $cut_off_payable_detail->subledger_type =$payable['type'];
            $cut_off_payable_detail->amount = number_format_db($payable['amount']);
            $cut_off_payable_detail->notes = $payable['notes'];

            $cut_off_payable_detail->save();
            array_push($coa_temp, $payable['coa_id']);
        }
        
        $coa = \Input::get('coa_id');
        for ($i=0; $i < count($coa); $i++) { 
            if (in_array($coa[$i], $coa_temp)) {
                continue;
            }

            if (\Input::get('amount')[$i]) {
                $cut_off_payable_detail = new CutOffPayableDetail;
                $cut_off_payable_detail->cut_off_payable_id = $cut_off_payable->id;
                $cut_off_payable_detail->coa_id = $coa[$i];
                $cut_off_payable_detail->subledger_id = 0;
                $cut_off_payable_detail->subledger_type = 0;
                $cut_off_payable_detail->amount = number_format_db(\Input::get('amount')[$i]);
                $cut_off_payable_detail->notes = '';

                $cut_off_payable_detail->save();    
            }
            
        }

		return $cut_off_payable;
    }
}

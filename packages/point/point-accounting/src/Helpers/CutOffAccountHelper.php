<?php

namespace Point\PointAccounting\Helpers;

use Point\Framework\Helpers\FormulirHelper;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffAccountDetail;
use Point\PointAccounting\Models\CutOffAccountSubledger;

class CutOffAccountHelper
{
    public static function searchList($list_cut_off, $date_from, $date_to, $search)
    {
        if ($date_from) {
            $list_cut_off = $list_cut_off->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_cut_off = $list_cut_off->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_cut_off = $list_cut_off->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                  ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_cut_off;
    }

    public static function create($formulir)
    {
        $cut_off_account = new CutOffAccount;
        $cut_off_account->formulir_id = $formulir->id;
        $cut_off_account->save();

        for ($i=0 ; $i<count(app('request')->input('coa_id')) ; $i++) {
            if (app('request')->input('debit')[$i] <= 0 && app('request')->input('credit')[$i] <= 0) {
                continue;
            }
            $cut_off_account_detail = new CutOffAccountDetail;
            $cut_off_account_detail->cut_off_account_id = $cut_off_account->id;
            $cut_off_account_detail->coa_id = app('request')->input('coa_id')[$i];
            $cut_off_account_detail->debit = number_format_db(app('request')->input('debit')[$i]);
            $cut_off_account_detail->credit = number_format_db(app('request')->input('credit')[$i]);
            $cut_off_account_detail->save();
        }
        
        return $cut_off_account;
    }
}

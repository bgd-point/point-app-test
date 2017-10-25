<?php

namespace Point\PointAccounting\Helpers;

use Point\Core\Helpers\TempDataHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\PointAccounting\Models\CutOff;
use Point\PointAccounting\Models\CutOffAccountSubledger;
use Point\PointAccounting\Models\CutOffInventory;
use Point\PointAccounting\Models\CutOffInventoryDetail;

class CutOffInventoryHelper
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
        $cut_off_inventory = new CutOffInventory;
        $cut_off_inventory->formulir_id = $formulir->id;
        $cut_off_inventory->save();

        $details = TempDataHelper::get('cut.off.inventory', auth()->user()->id);
        $coa_temp = [];
        foreach ($details as $inventory) {
            $cut_off_inventory_detail = new CutOffInventoryDetail;
            $cut_off_inventory_detail->cut_off_inventory_id = $cut_off_inventory->id;
            $cut_off_inventory_detail->coa_id = $inventory['coa_id'];
            $cut_off_inventory_detail->warehouse_id = $inventory['warehouse_id'];
            $cut_off_inventory_detail->subledger_id = $inventory['item_id'];
            $cut_off_inventory_detail->subledger_type =$inventory['type'];
            $cut_off_inventory_detail->stock_in_database = number_format_db($inventory['stock_in_db']);
            $cut_off_inventory_detail->stock = $inventory['stock'];
            $cut_off_inventory_detail->amount = number_format_db($inventory['amount']);
            $cut_off_inventory_detail->notes = $inventory['notes'];

            $cut_off_inventory_detail->save();
            array_push($coa_temp, $inventory['coa_id']);
        }

        $coa = \Input::get('coa_id');
        for ($i=0; $i < count($coa); $i++) {
            if (in_array($coa[$i], $coa_temp)) {
                continue;
            }

            if (\Input::get('amount')[$i]) {
                $cut_off_inventory_detail = new CutOffInventoryDetail;
                $cut_off_inventory_detail->cut_off_inventory_id = $cut_off_inventory->id;
                $cut_off_inventory_detail->coa_id = $coa[$i];
                $cut_off_inventory_detail->subledger_id = 0;
                $cut_off_inventory_detail->subledger_type = 0;
                $cut_off_inventory_detail->amount = number_format_db(\Input::get('amount')[$i]);
                $cut_off_inventory_detail->notes = '';

                $cut_off_inventory_detail->save();
            }
        }
        
        return $cut_off_inventory;
    }
}

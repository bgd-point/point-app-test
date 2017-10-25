<?php

namespace Point\PointAccounting\Helpers;

use Point\Core\Helpers\TempDataHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\FixedAssetsHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\FixedAssets;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffAccountSubledger;
use Point\PointAccounting\Models\CutOffFixedAssets;
use Point\PointAccounting\Models\CutOffFixedAssetsDetail;

class CutOffFixedAssetsHelper
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
        $cut_off_fixed_assets = new CutOffFixedAssets;
        $cut_off_fixed_assets->formulir_id = $formulir->id;
        $cut_off_fixed_assets->save();

        $details = TempDataHelper::get('cut.off.fixed.assets', auth()->user()->id);
        foreach ($details as $fixed_assets) {
            $cut_off_fixed_assets_detail = new CutOffFixedAssetsDetail;
            $cut_off_fixed_assets_detail->fixed_assets_id = $cut_off_fixed_assets->id;
            $cut_off_fixed_assets_detail->coa_id = $fixed_assets['coa_id'];
            $cut_off_fixed_assets_detail->date_purchased = $fixed_assets['date_purchased'];
            $cut_off_fixed_assets_detail->supplier_id =$fixed_assets['supplier_id'];
            $cut_off_fixed_assets_detail->name = $fixed_assets['name_asset'];
            $cut_off_fixed_assets_detail->country =$fixed_assets['country'];
            $cut_off_fixed_assets_detail->total_paid = $fixed_assets['total_paid'];
            $cut_off_fixed_assets_detail->quantity = $fixed_assets['quantity'];
            $cut_off_fixed_assets_detail->price = $fixed_assets['price'];
            $cut_off_fixed_assets_detail->total_price = $fixed_assets['total_price'];
            $cut_off_fixed_assets_detail->save();
        }
        
        return $cut_off_fixed_assets;
    }
}

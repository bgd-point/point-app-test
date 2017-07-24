<?php
namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\Core\Models\Vesa;
use Point\Framework\Models\Master\ItemUnit;
use Point\PointSales\Models\Pos\PosPricing;
use Point\PointSales\Models\Pos\PosPricingItem;

class PosPricingHelper
{
    public static function searchList($list_pos_pricing, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_pos_pricing = $list_pos_pricing->orderBy($order_by, $order_type);
        } else {
            $list_pos_pricing = $list_pos_pricing->orderByStandard();
        }

        $list_pos_pricing = $list_pos_pricing->where('formulir.form_status', '=', $status ?: 0);
        
        if ($date_from) {
            $list_pos_pricing = $list_pos_pricing->where('formulir.form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_pos_pricing = $list_pos_pricing->where('formulir.form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            $list_pos_pricing = $list_pos_pricing->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('formulir.notes', 'like', '%'.$search.'%');
            });
        }

        return $list_pos_pricing;
    }
}

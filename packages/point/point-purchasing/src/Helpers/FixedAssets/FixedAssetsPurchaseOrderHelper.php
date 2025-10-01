<?php

namespace Point\PointPurchasing\Helpers\FixedAssets;

use Illuminate\Http\Request;
use Point\Framework\Helpers\ReferHelper;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointExpedition\Models\ExpeditionOrderReferenceItem;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrderDetail;

class FixedAssetsPurchaseOrderHelper
{
    public static function searchList($list_purchase_order, $status, $date_from, $date_to, $search)
    {
        $list_purchase_order = $list_purchase_order->where('form_status', '=', $status ? : 0);
        if ($date_from) {
            $list_purchase_order = $list_purchase_order->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_purchase_order = $list_purchase_order->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_purchase_order = $list_purchase_order->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_purchase_order;
    }

    private static function getIncludeExpedition(Request $request)
    {
        if ($request->input('include_expedition')) {
            return 1;
        }

        return 0;
    }

    private static function getExpeditionFee(Request $request)
    {
        if ($request->input('include_expedition')) {
            return number_format_db($request->input('expedition_fee'));
        }

        return 0;
    }

    private static function getReference($request)
    {
        $reference_type = $request->input('reference_type');
        $reference_id = $request->input('reference_id');
        if ($reference_type != '') {
            $reference = $reference_type::find($reference_id);
            return $reference;
        }

        return null;
    }

    private static function getCashStatus(Request $request)
    {
        if ($request->input('is_cash')) {
            return 1;
        }
 
        return 0;
    }



    public static function create(Request $request, $formulir)
    {
        $reference = self::getReference($request);

        $purchase_order = new FixedAssetsPurchaseOrder;
        $purchase_order->formulir_id = $formulir->id;
        $purchase_order->supplier_id = $request->input('supplier_id');
        $purchase_order->type_of_tax = $request->input('type_of_tax');
        $purchase_order->include_expedition = self::getIncludeExpedition($request);
        $purchase_order->expedition_fee = self::getExpeditionFee($request);
        $purchase_order->is_cash = self::getCashStatus($request);
        $purchase_order->save();

        $subtotal = 0;
        $close_status = true;

        for ($i=0 ; $i < count($request->input('coa_id')) ; $i++) {
            $purchase_order_item = new FixedAssetsPurchaseOrderDetail;
            $purchase_order_item->fixed_assets_order_id = $purchase_order->id;
            $purchase_order_item->coa_id = $request->input('coa_id')[$i];
            $purchase_order_item->name = $request->input('name')[$i];
            $purchase_order_item->quantity = number_format_db($request->input('quantity')[$i]);
            $purchase_order_item->price = number_format_db($request->input('price')[$i]);
            $purchase_order_item->discount = number_format_db($request->input('item_discount')[$i]);
            $purchase_order_item->allocation_id = $request->input('allocation_id')[$i];
            $purchase_order_item->unit = $request->input('unit')[$i];
            $purchase_order_item->save();

            if ($reference != null) {
                ReferHelper::create(
                    $request->input('reference_item_type')[$i],
                    $request->input('reference_item_id')[$i],
                    get_class($purchase_order_item),
                    $purchase_order_item->id,
                    get_class($purchase_order),
                    $purchase_order->id,
                    $purchase_order_item->quantity
                );
            }
            
            // close form reference ( form purchase requisition) when checkbox close is chacked
            if ($request->input('close') == 'on' && $reference != null) {
                $close_status = ReferHelper::closeStatus(
                    $request->input('reference_item_type')[$i],
                    $request->input('reference_item_id')[$i],
                    $request->input('reference_item_value')[$i]
                );
            }

            $subtotal += ($purchase_order_item->quantity * $purchase_order_item->price) - ($purchase_order_item->quantity * $purchase_order_item->price / 100 * $purchase_order_item->discount);
        }

        $discount = number_format_db($request->input('discount'));
        $tax_base = $subtotal - ($subtotal / 100 * $discount);
        $tax = 0;

        if ($request->input('tax_type') == 'exclude') {
            $tax = $tax_base * 11 / 100;
        }
        if ($request->input('tax_type') == 'include') {
            $tax_base =  $tax_base * 100 / 111;
            $tax =  $tax_base * 11 / 100;
        }

        

        $purchase_order->subtotal = $subtotal;
        $purchase_order->discount = $discount;
        $purchase_order->tax_base = $tax_base;
        $purchase_order->tax = $tax;
        $purchase_order->total = $tax_base + $tax + $purchase_order->expedition_fee;
        $purchase_order->save();
        
        if ($reference) {
            self::updateStatusReference($request, $reference, $purchase_order);
        }
        
        $formulir->approval_to = $request->input('approval_to');
        $formulir->approval_status = 0;
        $formulir->save();
        
        return $purchase_order;
    }

    public static function updateStatusReference($request, $reference, $purchase_order)
    {
        // update status reference (purchase requisition) by remaining quantity
        formulir_lock($reference->formulir_id, $purchase_order->formulir_id);
        $close = false;
        foreach ($reference->details as $reference_item) {
            $remaining_quantity = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);
            $close = true;
            if ($remaining_quantity > 0) {
                $close = false;
                break;
            }
        }
        // update by form close manual
        if ($request->input('close') != null || $close === true) {
            $reference->formulir->form_status = 1;
        }

        $reference->formulir->save();
    }

    public static function registerToExpedition($purchase_order)
    {
        if ($purchase_order->include_expedition == 1) {
            return false;
        }
        
        $expedition_order = new ExpeditionOrderReference;
        $expedition_order->expedition_reference_id = $purchase_order->formulir_id;
        $expedition_order->person_id = $purchase_order->supplier_id;
        $expedition_order->type_of_tax = $purchase_order->type_of_tax;
        $expedition_order->include_expedition = $purchase_order->include_expedition;
        $expedition_order->expedition_fee = $purchase_order->expedition_fee;
        $expedition_order->subtotal = $purchase_order->subtotal;
        $expedition_order->discount = $purchase_order->discount;
        $expedition_order->tax_base = $purchase_order->tax_base;
        $expedition_order->tax = $purchase_order->tax;
        $expedition_order->total = $purchase_order->total;
        $expedition_order->is_cash = $purchase_order->is_cash;

        $expedition_order->save();
 
        foreach ($purchase_order->items as $item_order) {
            $expedition_item = new ExpeditionOrderReferenceItem;
            $expedition_item->point_expedition_order_reference_id = $expedition_order->id;
            $expedition_item->item_id = $item_order->item_id;
            $expedition_item->allocation_id = $item_order->allocation_id;
            $expedition_item->quantity = number_format_db($item_order->quantity);
            $expedition_item->price = number_format_db($item_order->price);
            $expedition_item->discount = number_format_db($item_order->discount);
            $expedition_item->unit = $item_order->unit;
            $expedition_item->converter = number_format_db($item_order->converter);
            $expedition_item->save();
        }
    }
}

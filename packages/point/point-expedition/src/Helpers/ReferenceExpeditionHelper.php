<?php

namespace Point\PointExpedition\Helpers;

use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointExpedition\Models\ExpeditionOrderReferenceItem;

class ReferenceExpeditionHeleper
{
    public static function removeReference($formulir_id)
    {
        $reference = ExpeditionOrderReference::where('expedition_reference_id', $formulir_id);
        $reference->delete();
    }

    private static function createReference($order)
    {
        $expedition_order = new ExpeditionOrderReference;
        $expedition_order->expedition_reference_id = $order->formulir_id;
        $expedition_order->person_id = $order->person_id;
        $expedition_order->type_of_tax = $order->type_of_tax;
        $expedition_order->include_expedition = $order->include_expedition;
        $expedition_order->expedition_fee = $order->expedition_fee;
        $expedition_order->subtotal = $order->subtotal;
        $expedition_order->discount = $order->discount;
        $expedition_order->tax_base = $order->tax_base;
        $expedition_order->tax = $order->tax;
        $expedition_order->total = $order->total;
        $expedition_order->save();

        foreach ($order->items as $item_order) {
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

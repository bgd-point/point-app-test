<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\UserWarehouse;
use Point\PointExpedition\Helpers\ExpeditionOrderHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderGroupDetail;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\GoodsReceivedItem;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseOrderItem;

class GoodsReceivedHelper
{
    public static function searchList($list_goods_received, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_goods_received = $list_goods_received->orderBy($order_by, $order_type);
        } else {
            $list_goods_received = $list_goods_received->orderByStandard();
        }

        if ($status != 'all') {
            $list_goods_received = $list_goods_received->where('formulir.form_status', '=', $status ?: 0);
        }

        if ($date_from) {
            $list_goods_received = $list_goods_received->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_goods_received = $list_goods_received->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_goods_received = $list_goods_received->where(function ($q) use ($search) {
                $q->where('formulir.form_number', 'like', '%'.$search.'%')
                    ->orWhere('person.name', 'like', '%'.$search.'%');
            });
        }

        return $list_goods_received;
    }

    private static function getReference($request)
    {
        $reference_type = $request->input('reference_purchase_order');
        $reference_id = $request->input('reference_purchase_order_id');
        return $reference_type::find($reference_id);
    }

    public static function create(Request $request, $formulir)
    {
        $reference = self::getReference($request);
        $goods_received = new GoodsReceived;
        $goods_received->formulir_id = $formulir->id;
        $goods_received->warehouse_id = $request->input('warehouse_id');
        $goods_received->supplier_id = $reference->supplier_id;
        $goods_received->driver = $request->input('driver');
        $goods_received->license_plate = $request->input('license_plate');
        $goods_received->point_purchasing_order_id = $request->input('reference_purchase_order_id');
        $goods_received->include_expedition = $request->input('include_expedition') ? 1 : 0;
        $goods_received->type_of_tax = $reference->type_of_tax;
        $goods_received->discount = $reference->discount;
        $goods_received->save();

        $goods_received->formulir->save();
        $subtotal = 0;
        for ($i=0 ; $i<count($request->input('item_id')) ; $i++) {
            if (number_format_db($request->input('item_quantity')[$i]) > number_format_db($request->input('item_quantity_reference')[$i])) {
                throw new PointException('Your Goods Received quantity not matched');
            }

            $goods_received_item = new GoodsReceivedItem;
            $goods_received_item->point_purchasing_goods_received_id = $goods_received->id;
            $goods_received_item->item_id = $request->input('item_id')[$i];
            $goods_received_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $goods_received_item->price = number_format_db($request->input('item_price')[$i]);
            $goods_received_item->discount = number_format_db($request->input('item_discount')[$i]);
            $goods_received_item->unit = $request->input('item_unit_name')[$i];
            $goods_received_item->converter = number_format_db($request->input('item_unit_converter')[$i]);
            $goods_received_item->allocation_id = 1;
            $goods_received_item->save();
            ReferHelper::create(
                $request->input('reference_item_type')[$i],
                $request->input('reference_item_id')[$i],
                get_class($goods_received_item),
                $goods_received_item->id,
                get_class($goods_received),
                $goods_received->id,
                $goods_received_item->quantity
            );

            $subtotal += $goods_received_item->quantity * $goods_received_item->price - $goods_received_item->quantity * $goods_received_item->price * $goods_received_item->discount / 100;
        }

        $discount_value = $subtotal * $goods_received->discount / 100;
        $tax_base = $subtotal - $discount_value;
        $tax = 0;

        if ($goods_received->type_of_tax == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }
        if ($goods_received->type_of_tax == 'include') {
            $tax_base = $tax_base * 100 / 110;
            $tax = $tax_base * 10 / 100;
        }
        
        $total_quantity_reference = PurchaseOrderItem::where('point_purchasing_order_id', $reference->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
        $total_quantity_received = GoodsReceivedItem::where('point_purchasing_goods_received_id', $goods_received->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
        $average_expedition_fee = $reference->expedition_fee * $total_quantity_received / $total_quantity_reference;
        
        $goods_received->expedition_fee = $average_expedition_fee;
        $goods_received->subtotal = $subtotal;
        $goods_received->tax_base = $tax_base;
        $goods_received->tax = $tax;
        $goods_received->total = $tax_base + $tax + $average_expedition_fee;
        $goods_received->save();


        /**
         * Locking process
         * - when reference from expedition order, locked_id = expedition formulir
         * - when reference from purchase order, locked_id = purchasing formulir
         */
        if ($request->input('group_expedition_order') != '') {
            $list_expedition_order = ExpeditionOrder::joinFormulir()->approvalApproved()->notArchived()->selectOriginal()->where('done', 0)->where('form_reference_id', $reference->formulir_id)->where('group', $request->input('group_expedition_order'))->get();
            foreach ($list_expedition_order as $expedition_order) {
                formulir_lock($expedition_order->formulir_id, $goods_received->formulir_id);
                $expedition_order->done = 1;
                $expedition_order->save();
            }
        } else {
            formulir_lock($reference->formulir_id, $goods_received->formulir_id);
        }

        // update status reference
        self::updateStatusReference($request, $reference);

        // update expedition reference
        if ($request->input('reference_expedition_order_id')) {
            self::updateExpeditionReference($request->input('reference_expedition_order_id'));
        }

        if ($reference->include_expedition) {
            self::journal($goods_received, $request, $reference, $average_expedition_fee);
        } else {
            self::transferItem($request);
        }
        
        JournalHelper::checkJournalBalance($goods_received->formulir_id);
        return $goods_received;
    }

    public static function transferItem($request)
    {
        $expedition_order_group_detail = ExpeditionOrderGroupDetail::where('point_expedition_order_id', $request->input('reference_expedition_order_id'))->first();
        $form_journal_inventory = $expedition_order_group_detail->group->formulir;
        $inventories = Inventory::where('formulir_id', $form_journal_inventory->id)->get();
        foreach ($inventories as $inventory) {
            $inventory->warehouse_id = $request->input('warehouse_id');
            $inventory->save();
        }
    }
    
    public static function journal($goods_received, $request, $reference, $expedition_fee)
    {
        // $reference is Purchasing
        $total_quantity = GoodsReceivedItem::where('point_purchasing_goods_received_id', $goods_received->id)->selectRaw('sum(quantity) as quantity')->first()->quantity;
        
        foreach ($goods_received->items as $goods_received_item) {
            $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
            // Journal inventory
            $total_per_row = $goods_received_item->quantity * $goods_received_item->price - $goods_received_item->quantity * $goods_received_item->price / 100 * $goods_received_item->discount;
            if ($goods_received->discount) {
                $discounty = $total_per_row * $goods_received->discount / 100;
                $total_per_row = $total_per_row - $discounty;
            }

            if ($goods_received->type_of_tax == 'include') {
                $total_per_row = $total_per_row * 100 / 110;
            }

            $position = JournalHelper::position($goods_received_item->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = $goods_received->formulir->form_date;
            $journal->coa_id = $goods_received_item->item->account_asset_id;
            $journal->description = 'Goods Received [' . $goods_received->formulir->form_number.']';
            $journal->$position = $total_per_row + $expedition_fee * $goods_received_item->quantity / $total_quantity;
            $journal->form_journal_id = $goods_received->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $goods_received_item->item_id;
            $journal->subledger_type = get_class($goods_received_item);
            $journal->save();

            \Log::info('sedian '. $position.' '.$journal->$position);
            // insert new inventory
            $item = Item::find($goods_received_item->item_id);
            $inventory = new Inventory();
            $inventory->formulir_id = $goods_received->formulir->id;
            $inventory->item_id = $item->id;
            $inventory->quantity = $goods_received_item->quantity * $goods_received_item->converter;
            $inventory->price = $goods_received_item->price / $goods_received_item->converter;
            $inventory->form_date = $goods_received->formulir->form_date;
            $inventory->warehouse_id = $request->input('warehouse_id');

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->in();
        }

        // 1. Journal account receiveable
        $account_receiveable = JournalHelper::getAccount('point purchasing', 'account payable');
        $position = JournalHelper::position($account_receiveable);
        $journal = new Journal;
        $journal->form_date = $goods_received->formulir->form_date;
        $journal->coa_id = $account_receiveable;
        $journal->description = 'Goods Received Purchasing [' . $goods_received->formulir->form_number.']';
        $journal->$position = $goods_received->total;
        $journal->form_journal_id = $goods_received->formulir->id;
        $journal->form_reference_id;
        $journal->subledger_id = $goods_received->supplier_id;
        $journal->subledger_type = get_class($goods_received->supplier);
        $journal->save();
        \Log::info('hutang '. $position.' ' .$journal->$position);

        // 2. Journal income tax receiveable
        if ($goods_received->tax > 0) {
            $income_tax_receiveable = JournalHelper::getAccount('point purchasing', 'income tax receivable');
            $position = JournalHelper::position($income_tax_receiveable);
            $journal = new Journal;
            $journal->form_date = $goods_received->formulir->form_date;
            $journal->coa_id = $income_tax_receiveable;
            $journal->description = 'Goods Received Purchasing [' . $goods_received->formulir->form_number.']';
            $journal->$position = $goods_received->tax;
            $journal->form_journal_id = $goods_received->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            \Log::info('tax '. $position.' ' .$journal->$position);
        }
    }

    public static function updateStatusReference($request, $reference)
    {
        // update by remaining quantity
        $close = false;
        foreach ($reference->items as $reference_item) {
            $remaining_quantity = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);
            $close = false;
            if ($remaining_quantity == 0) {
                $close = true;
            }
        }
        // update by form close manual
        if ($request->input('close') != null || $close === true) {
            $reference->formulir->form_status = 1;
        }

        $reference->formulir->save();
    }

    public static function updateExpeditionReference($expedition_order_id)
    {
        $expedition_order = ExpeditionOrder::find($expedition_order_id);
        $purchase_order = PurchaseOrder::where('formulir_id', $expedition_order->form_reference_id)->first();
        $finish = false;
        foreach ($expedition_order->items as $expedition_order_item) {
            $purchase_order_item = PurchaseOrderItem::where('point_purchasing_order_id', $purchase_order->id)->where('item_id', $expedition_order_item->item_id)->first();
            $remaining = ReferHelper::remaining(get_class($purchase_order_item), $purchase_order_item->id, $purchase_order_item->quantity);
            if ($remaining == 0) {
                $finish = true;
            }
        }
        if ($finish && !$expedition_order->include_expedition) {
            $expedition_order_reference = ExpeditionOrderReference::where('expedition_reference_id', $expedition_order->form_reference_id)->first();
            $expedition_order_reference->finish =  1;
            $expedition_order_reference->save();
        }
    }

    public static function undoneExpedition($goods_received)
    {
        $formulir_locks = FormulirLock::where('locking_id', '=', $goods_received->formulir_id)->get();
        foreach ($formulir_locks as $formulir_lock) {
            $formulir = Formulir::find($formulir_lock->locked_id);
            if ($formulir->formulirable_type == get_class(new ExpeditionOrder())) {
                $expedition_order = ExpeditionOrder::find($formulir->formulirable_id);
                $expedition_order->done = 0;
                $expedition_order->save();
            }
        }
    }
}

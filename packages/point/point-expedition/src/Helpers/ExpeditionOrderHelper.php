<?php

namespace Point\PointExpedition\Helpers;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Models\Vesa;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Helpers\WarehouseHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderGroup;
use Point\PointExpedition\Models\ExpeditionOrderGroupDetail;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointExpedition\Models\ExpeditionOrderReferenceItem;
use Point\PointExpedition\Models\ExpeditionRequisition;
use Point\PointExpedition\Models\PurchaseRequisitionItem;
use Point\PointPurchasing\Helpers\GoodsReceivedHelper;
use Point\PointPurchasing\Models\PurchaseOrder;
use Point\PointSales\Models\Sales\SalesOrder;

class ExpeditionOrderHelper
{
    public static function searchList($list_expedition_order, $order_by, $order_type, $status, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_expedition_order = $list_expedition_order->where('formulir.form_status', '=', $status ? : 0);
        }

        if ($order_by) {
            $list_expedition_order = $list_expedition_order->orderBy($order_type, $order_by);
        } else {
            $list_expedition_order = $list_expedition_order->orderByStandard();
        }

        if ($date_from) {
            $list_expedition_order = $list_expedition_order->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_expedition_order = $list_expedition_order->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_expedition_order = $list_expedition_order->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%' . $search . '%')
                    ->orWhere('formulir.form_number', 'like', '%' . $search . '%');
            });
        }

        return $list_expedition_order;
    }

    public static function create(Request $request, $formulir)
    {
        $reference = self::getReference($request);
        $expedition_order = new ExpeditionOrder;
        $expedition_order->formulir_id = $formulir->id;
        $expedition_order->expedition_id = $request->input('expedition_id');
        $expedition_order->group = self::getGroup($request);
        $expedition_order->form_reference_id = $reference->formulir_id;
        $expedition_order->type_of_fee = '';
        $expedition_order->expedition_fee = number_format_db($request->input('subtotal'));
        $expedition_order->delivery_date = date_format_db($request->input('form_date'), $request->input('time'));
        $expedition_order->type_of_tax = $request->input('type_of_tax');
        $expedition_order->discount = number_format_db($request->input('discount'));
        
        $discount_value = $expedition_order->expedition_fee * $expedition_order->discount / 100;
        $tax_base = $expedition_order->expedition_fee - $discount_value;
        $tax = 0;

        if ($expedition_order->type_of_tax == 'exclude') {
            $tax = $tax_base * 10 / 100;
        }
        if ($expedition_order->type_of_tax == 'include') {
            $tax_base = $tax_base * 100 / 110;
            $tax = $tax_base * 10 / 100;
        }

        $expedition_order->tax_base = $tax_base;
        $expedition_order->tax = $tax;
        $expedition_order->total = $tax_base + $tax;
        $expedition_order->save();

        for ($i = 0; $i < count($request->input('item_id')); $i++) {
            // validate quantity
            $available_quantity = self::availableQuantity($expedition_order->form_reference_id, $request->input('item_id')[$i]);
            if (! $request->input('group')) {
                if ($request->input('item_quantity')[$i] > $available_quantity) {
                    throw new PointException('QUANTITY OF DELIVERY IS BIGGER THAN AVAILABLE QUANTITY');
                }
            }

            $expedition_order_item = new ExpeditionOrderItem;
            $expedition_order_item->point_expedition_order_id = $expedition_order->id;
            $expedition_order_item->item_id = $request->input('item_id')[$i];
            $expedition_order_item->quantity = number_format_db($request->input('item_quantity')[$i]);
            $expedition_order_item->unit = $request->input('item_unit_name')[$i];
            $expedition_order_item->price = $request->input('price')[$i];
            $expedition_order_item->item_fee = 0;
            $expedition_order_item->converter = 1;
            $expedition_order_item->save();
        }

        formulir_lock($reference->formulir_id, $expedition_order->formulir_id);
        return $expedition_order;
    }

    private static function getReference($request)
    {
        $reference_type = $request->input('reference_type');
        $reference_id = $request->input('reference_id');
        return $reference_type::find($reference_id);
    }

    private static function getGroup($request)
    {
        if ($request->input('group')) {
            $expedition_order = ExpeditionOrder::find($request->input('group'));
            return $expedition_order->group;
        }

        $expedition_order = ExpeditionOrder::joinFormulir()->notArchived()->approvalApproved()->where('form_reference_id', $request->input('reference_formulir_id'))->groupBy('group')->max('group');
        return $expedition_order + 1;
    }

    public static function originalQuantityReference($form_reference_id, $item_id)
    {
        /**
         * Get quantity item expedition reference
         */
        $expedition_reference_detail = ExpeditionOrderReferenceItem::join('point_expedition_order_reference', 'point_expedition_order_reference.id', '=', 'point_expedition_order_reference_item.point_expedition_order_reference_id')
            ->where('point_expedition_order_reference.expedition_reference_id', $form_reference_id)
            ->where('item_id', $item_id)
            ->first();
            
        return $expedition_reference_detail->quantity;
    }

    public static function totalQuantityExpeditionItemDelivered($form_reference_id, $item_id)
    {
        /**
         * get quantity expedition order item
         */
        $array_expedition_order_id = ExpeditionOrder::joinFormulir()->notArchived()->notCanceled()->approvalApproved()->where('form_reference_id', $form_reference_id)->groupBy('group')->select('point_expedition_order.id')->get()->toArray();
        $quantity_expedition_order_item = ExpeditionOrderItem::whereIn('point_expedition_order_id', $array_expedition_order_id)->where('item_id', $item_id)->groupBy('item_id')->sum('quantity');

        return $quantity_expedition_order_item;
    }

    public static function availableQuantity($form_reference_id, $item_id)
    {
        $difference = self::originalQuantityReference($form_reference_id, $item_id) - self::totalQuantityExpeditionItemDelivered($form_reference_id, $item_id);
        return $difference;
    }
    
    public static function getToExpeditionOrder()
    {
        $list_purchase_order = PurchaseOrder::joinFormulir()->availableToPickup()->selectOriginal()->get()->toArray();
        $list_sales_order = SalesOrder::joinFormulir()->availableToPickup()->selectOriginal()->get()->toArray();

        $expedition_order = [];
        if ($list_purchase_order) {
            array_push($expedition_order, $list_purchase_order);
        }

        if ($list_sales_order) {
            array_push($expedition_order, $list_sales_order);
        }

        return $expedition_order;
    }

    public static function cancelExpeditionReference($formulir_id)
    {
        $formulir = Formulir::find($formulir_id);
        $expedition_reference = ExpeditionOrderReference::where('expedition_order_id', '=', $formulir->formulirable_id)->first();
        if ($expedition_reference) {
            $expedition_reference->expedition_order_id = null;
            $expedition_reference->save();
        }
    }

    public static function journalExpeditionOrder($id)
    {
        $expedition_order = ExpeditionOrder::find($id);

        $list_expedition_order = ExpeditionOrder::joinFormulir()->approvalApproved()->notArchived()->where('group', $expedition_order->group)->where('form_reference_id', $expedition_order->form_reference_id)->selectOriginal();
        
        $form_date = date_format_db(date('d-m-Y'), 'start');
        $form_number = FormulirHelper::number('point-expedition-group', $form_date);

        $formulir = new Formulir;
        $formulir->form_date = $form_date;
        $formulir->form_number = $form_number['form_number'];
        $formulir->form_raw_number = $form_number['raw'];
        $formulir->approval_to = 1;
        $formulir->approval_status = 1;
        $formulir->form_status = 1;
        $formulir->created_by = auth()->user()->id;
        $formulir->updated_by = auth()->user()->id;
        $formulir->save();

        $group = new ExpeditionOrderGroup;
        $group->formulir_id = $formulir->id;
        $group->save();

        $total_fee = 0;
        foreach ($list_expedition_order->get() as $expedition_order) {
            $expedition_order->is_finish = 1;
            $expedition_order->save();

            $group_detail = new ExpeditionOrderGroupDetail;
            $group_detail->point_expedition_order_group_id = $group->id;
            $group_detail->point_expedition_order_id = $expedition_order->id;
            $group_detail->save();

            $tax_base = $expedition_order->tax_base;
            $total = $expedition_order->total;

            $total_fee += $tax_base;

            // Journal Account Payable Expedition
            $account_payable_expedition = JournalHelper::getAccount('point expedition', 'account payable - expedition');
            $position = JournalHelper::position($account_payable_expedition);
            $journal = new Journal;
            $journal->form_date = $group->formulir->form_date;
            $journal->coa_id = $account_payable_expedition;
            $journal->description = 'expedition order "' . $group->formulir->form_number . '"';
            $journal->$position = $total;
            $journal->form_journal_id = $group->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $expedition_order->expedition_id;
            $journal->subledger_type = get_class(new Person);
            $journal->save();
            \Log::info('utang exp '. $position.' '. $total);

            // Journal Income Tax Expedition
            if ($expedition_order->tax != 0) {
                $income_tax_payable = JournalHelper::getAccount('point expedition', 'income tax receivable');
                $position = JournalHelper::position($income_tax_payable);
                $journal = new Journal();
                $journal->form_date = $group->formulir->form_date;
                $journal->coa_id = $income_tax_payable;
                $journal->description = 'expedition order "' . $group->formulir->form_number . '"';
                $journal->$position = $expedition_order->tax;
                $journal->form_journal_id = $group->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();

                \Log::info('tax expedition_order '. $position.' '. $expedition_order->tax);
            }
        }

        $form_reference = Formulir::find($expedition_order->form_reference_id);
        $reference = $form_reference->formulirable_type::find($form_reference->formulirable_id);
        if (! $reference->supplier_id) {
            $reference->person_id = $reference->person_id;
        } else{
            $reference->person_id = $reference->supplier_id;
        }

        $continue = false;
        // $total_quantity_expedition = ExpeditionOrderItem::where('point_expedition_order_id', $list_expedition_order->first()->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
        $subtotal_reference = 0;
        $list_expedition_order_tmp = $list_expedition_order->get();
        foreach ($list_expedition_order->first()->items as $expedition_order_item) {
            // Journal Inventory
            $item_purchase_per_row = 0;
            $item_expedition_per_row = 0;

            $item_purchase_per_row = $expedition_order_item->quantity * $expedition_order_item->price;
            // $expedition_fee_per_item = $total_fee * $expedition_order_item->quantity / $total_quantity_expedition;
            if ($reference->discount) {
                $discounty = $item_purchase_per_row * $reference->discount / 100;
                $item_purchase_per_row = $item_purchase_per_row - $discounty;
            }

            if ($reference->type_of_tax == 'include') {
                $item_purchase_per_row = $item_purchase_per_row * 100 / 110;
            }
            \Log::info('purchase per row ' .$item_purchase_per_row);
            $subtotal_reference += $expedition_order_item->quantity * $expedition_order_item->price;
            foreach ($list_expedition_order_tmp as $expedition_order) {
                $total_quantity_expedition = ExpeditionOrderItem::where('point_expedition_order_id', $expedition_order->id)->selectRaw('sum(quantity) as quantity')->first()->quantity; 
                $item_expedition_per_row += $expedition_order_item->quantity * $expedition_order->tax_base / $total_quantity_expedition;
                \Log::info('exp per row ' .$item_expedition_per_row);
            }


            $position = JournalHelper::position($expedition_order_item->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = $group->formulir->form_date;
            $journal->coa_id = $expedition_order_item->item->account_asset_id;
            $journal->description = 'expedition order [' . $group->formulir->form_number.']';
            $journal->$position = $item_purchase_per_row + $item_expedition_per_row;
            $journal->form_journal_id = $group->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $expedition_order_item->item_id;
            $journal->subledger_type = get_class($expedition_order_item->item);
            $journal->save();
            \Log::info('sediaan '. $position.' '. $journal->$position);

            $warehouse = Warehouse::where('name', 'in transit')->first();
            if (!$warehouse) {
                $warehouse = self::createWarehouse();
            }
            if (! $continue) {
                $inventory = new Inventory();
                $inventory->formulir_id = $group->formulir->id;
                $inventory->item_id = $expedition_order_item->item_id;
                $inventory->quantity = $expedition_order_item->quantity * $expedition_order_item->converter;
                $inventory->price = $expedition_order_item->price / $expedition_order_item->converter;
                $inventory->form_date = $group->formulir->form_date;
                $inventory->warehouse_id = $warehouse->id;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();

                $available_quantity = self::availableQuantity($list_expedition_order->first()->form_reference_id, $expedition_order_item->item_id);
                $is_finish = $available_quantity == 0 ? true : false;
            }
        }

        /**
         * ACCOUNT PAYABLE REFERENCE
         */ 
        
        $reference_discount_value = $subtotal_reference * $reference->discount / 100;
        $reference_tax_base = $subtotal_reference - $reference_discount_value;
        $reference_tax = 0;

        if ($reference->type_of_tax == 'exclude') {
            $reference_tax = $reference_tax_base * 10 / 100;
        }
        if ($reference->type_of_tax == 'include') {
            $reference_tax_base = $reference_tax_base * 100 / 110;
            $reference_tax = $reference_tax_base * 10 / 100;
        }

        $reference_total = $reference_tax + $reference_tax_base;

        // Journal Account Payable Purchasing
        $account_receiveable = JournalHelper::getAccount('point purchasing', 'account payable');
        $position = JournalHelper::position($account_receiveable);
        $journal = new Journal;
        $journal->form_date = $group->formulir->form_date;
        $journal->coa_id = $account_receiveable;
        $journal->description = 'expedition order [' . $group->formulir->form_number.']';
        $journal->$position = $reference_total;
        $journal->form_journal_id = $group->formulir_id;
        $journal->form_reference_id;
        $journal->subledger_id = $reference->person_id;
        $journal->subledger_type = get_class(new Person);
        $journal->save();
        \Log::info('utang purchasing '. $position. ' ' .$journal->$position);

        if ($reference->tax > 0) {
            $income_tax_receiveable = JournalHelper::getAccount('point purchasing', 'income tax receivable');
            $position = JournalHelper::position($income_tax_receiveable);
            $journal = new Journal;
            $journal->form_date = $reference->formulir->form_date;
            $journal->coa_id = $income_tax_receiveable;
            $journal->description = 'expedition order [' . $reference->formulir->form_number.']';
            $journal->$position = $reference_tax;
            $journal->form_journal_id = $group->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            \Log::info('tax purchase '. $position.' ' .$journal->$position);

        }

        JournalHelper::checkJournalBalance($group->formulir_id);

        // update expedition reference 
        if ($is_finish) {
            $expedition_reference = ExpeditionOrderReference::where('expedition_reference_id', $expedition_order->form_reference_id)->first();
            $expedition_reference->finish = 1;
            $expedition_reference->save();
        }

        $journal = Journal::where('form_journal_id', $group->formulir_id)->get();
    }

    public static function createWarehouse()
    {
        $warehouse = new Warehouse;
        $warehouse->code = WarehouseHelper::getLastCode();
        $warehouse->name = 'in transit';
        $warehouse->created_by = auth()->user()->id;
        $warehouse->disabled = 1;
        $warehouse->save();

        return $warehouse;
    }

    /**
     * Remove Journal form expedition
     * - table journal
     * - table account_payable_and_receivable
     * - table inventory
     */
    public static function removeJournal($expedition_order)
    {
        $expedition_order_group_item = ExpeditionOrderGroupDetail::where('point_expedition_order_id', $expedition_order->id)->first();
        if (!$expedition_order_group_item) {
            return true;
        }

        foreach ($expedition_order_group_item->group->details as $group_detail) {
            $expedition_order_group = ExpeditionOrder::find($group_detail->point_expedition_order_id);
            $expedition_order_group->is_finish = 0;
            $expedition_order_group->save();
        }

        Journal::where('form_journal_id', $expedition_order_group_item->group->formulir_id)->delete();
        AccountPayableAndReceivable::where('formulir_reference_id', $expedition_order_group_item->group->formulir_id)->delete();
        Inventory::where('formulir_id', $expedition_order_group_item->group->formulir_id)->delete();
        ExpeditionOrderGroup::where('formulir_id', $expedition_order_group_item->group->formulir_id)->delete();

        $reference = $expedition_order->reference();
        $expedition_reference = ExpeditionOrderReference::where('expedition_reference_id', $reference->formulir_id)->first();
        $expedition_reference->finish = 0;
        $expedition_reference->save();
        return true;
    }
}

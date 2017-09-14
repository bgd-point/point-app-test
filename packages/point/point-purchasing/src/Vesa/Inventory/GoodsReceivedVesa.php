<?php

namespace Point\PointPurchasing\Vesa\Inventory;

use Point\PointPurchasing\Models\Inventory\PurchaseOrder;

trait GoodsReceivedVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreate();
        $array = self::vesaApproval($array);
        $array = self::vesaReject($array);

        return $array;
    }

    public static function getVesaCreate()
    {
        return self::vesaCreate([], false);
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaReject()
    {
        return self::vesaReject([], false);
    }
    
    private static function vesaCreate($array=[], $merge_into_group = true)
    {
        $array_formulir_expedition_locked_by_goods_received = PurchaseOrder::getArrayFormulirExpeditionOrderLockedByGoodsReceived();
        $array_formulir_purchase_order_locked_by_expedition = PurchaseOrder::getArrayFormulirPurchaseOrderLockedByExpedition();
        $array_expedition_order_locked_by_purchase_order = PurchaseOrder::getArrayExpeditionOrderLockedPurchaseOrder($array_formulir_purchase_order_locked_by_expedition);

        $list_purchase_order = PurchaseOrder::joinFormulir()->notArchived()->open()->approvalApproved()->selectOriginal()->orderByStandard();
        if (($array_expedition_order_locked_by_purchase_order === $array_formulir_expedition_locked_by_goods_received) && ($array_formulir_expedition_locked_by_goods_received)) {
            $list_purchase_order = PurchaseOrder::joinFormulir()->open()->approvalApproved()->notArchived()->whereNotIn('formulir_id', $array_formulir_purchase_order_locked_by_expedition)->selectOriginal()->orderByStandard();
        }

        // Grouping vesa
        if ($merge_into_group && $list_purchase_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/goods-received/vesa-create'),
                'deadline' => $list_purchase_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Please create goods received',
                'permission_slug' => 'create.point.purchasing.goods.received'
            ]);

            return $array;
        }
        // Push all
        foreach ($list_purchase_order->get() as $purchase_order) {
            $expedition_reference = $purchase_order->checkExpeditionReference($purchase_order->formulir_id);

            if ((!$purchase_order->is_cash) && ($purchase_order->include_expedition)) {
                array_push($array, [
                    'url' => url('purchasing/point/goods-received/create-step-4/'.$purchase_order->id),
                    'deadline' => $purchase_order->formulir->form_date,
                    'message' => 'create goods received from ' . $purchase_order->formulir->form_number,
                    'permission_slug' => 'create.point.purchasing.goods.received'
                ]);

                continue;
                // output => create goods received
            }

            if (($purchase_order->is_cash) && ($purchase_order->include_expedition) && ($purchase_order->getTotalRemainingDownpayment($purchase_order->id) > 0)) {
                array_push($array, [
                    'url' => url('purchasing/point/goods-received/create-step-4/'.$purchase_order->id),
                    'deadline' => $purchase_order->formulir->form_date,
                    'message' => 'create goods received from ' . $purchase_order->formulir->form_number,
                    'permission_slug' => 'create.point.purchasing.goods.received'
                ]);

                continue;
                // output => create downpayment
            }

            if ((!$purchase_order->is_cash) && (!$purchase_order->include_expedition) && ($expedition_reference)) {
                array_push($array, [
                    'url' => url('purchasing/point/goods-received/create-step-3/'.$purchase_order->id),
                    'deadline' => $purchase_order->formulir->form_date,
                    'message' => 'create goods received from ' . $purchase_order->formulir->form_number,
                    'permission_slug' => 'create.point.purchasing.goods.received'
                ]);

                continue;
                // output => create expedition order
            }
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_goods_received = self::joinFormulir()->open()->approvalPending()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_goods_received->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/goods-received/vesa-approval'),
                'deadline' => $list_goods_received->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve purchase goods received',
                'permission_slug' => 'approval.point.purchasing.goods.received'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_goods_received->get() as $goods_received) {
            array_push($array, [
                'url' => url('purchasing/point/goods-received/' . $goods_received->id),
                'deadline' => $goods_received->formulir->form_date,
                'message' => 'please approve this purchase goods received ' . $goods_received->formulir->form_number,
                'permission_slug' => 'approval.point.purchasing.goods.received'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_goods_received = self::joinFormulir()->open()->approvalRejected()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_goods_received->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/goods-received/vesa-rejected'),
                'deadline' => $list_goods_received->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.goods.received'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_goods_received->get() as $goods_received) {
            array_push($array, [
                'url' => url('purchasing/point/goods-received/' . $goods_received->id.'/edit'),
                'deadline' => $goods_received->formulir->form_date,
                'message' => $goods_received->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.goods.received'
            ]);
        }

        return $array;
    }
}

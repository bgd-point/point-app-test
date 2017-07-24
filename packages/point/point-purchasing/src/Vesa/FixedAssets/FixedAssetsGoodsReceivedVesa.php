<?php

namespace Point\PointPurchasing\Vesa\FixedAssets;

use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;

trait FixedAssetsGoodsReceivedVesa
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

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $array_purchase_order = [];

        $list_purchase_include_expedition = FixedAssetsPurchaseOrder::includeExpedition()->get();
        foreach ($list_purchase_include_expedition as $purchase_include_expedition) {
            array_push($array_purchase_order, $purchase_include_expedition->id);
        }

        $list_purchase_exclude_expedition = FixedAssetsPurchaseOrder::excludeExpedition()->get();
        foreach ($list_purchase_exclude_expedition as $purchase_exclude_expedition) {
            array_push($array_purchase_order, $purchase_exclude_expedition->id);
        }

        $list_purchasing_order = FixedAssetsPurchaseOrder::joinFormulir()->whereIn('point_purchasing_fixed_assets_order.id', $array_purchase_order)->selectOriginal();
        // Grouping vesa
        if ($merge_into_group && $list_purchasing_order->count() > 5) {
            foreach ($list_purchasing_order->get() as $purchasing_order) {
                array_push($array, [
                    'url' => url('purchasing/point/fixed-assets/goods-received/vesa-create'),
                    'deadline' => $purchasing_order->orderBy('required_date')->first()->required_date,
                    'message' => 'Make an purchasing goods received',
                    'permission_slug' => 'create.point.purchasing.goods.received.fixed.assets'
                ]);
            }
            return $array;
        }

        // Push all
        foreach ($list_purchasing_order->get() as $purchasing_order) {
            if ($purchasing_order->is_cash) {
                if ($purchasing_order->getTotalRemainingDownpayment($purchasing_order->id) > 0) {
                    array_push($array, [
                        'url' => url('purchasing/point/fixed-assets/goods-received/create-step-2/'.$purchasing_order->id),
                        'deadline' => $purchasing_order->required_date ? : $purchasing_order->formulir->form_date,
                        'message' => 'Make an purchasing goods received from purchasing order' . $purchasing_order->formulir->form_number,
                        'permission_slug' => 'create.point.purchasing.goods.received.fixed.assets'
                    ]);
                }    
            } else {
                array_push($array, [
                    'url' => url('purchasing/point/fixed-assets/goods-received/create-step-2/'.$purchasing_order->id),
                    'deadline' => $purchasing_order->required_date ? : $purchasing_order->formulir->form_date,
                    'message' => 'Make an purchasing goods received from purchasing order' . $purchasing_order->formulir->form_number,
                    'permission_slug' => 'create.point.purchasing.goods.received.fixed.assets'
                ]);
            }
            
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_goods_received = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_goods_received->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/goods-received/vesa-approval'),
                'deadline' => $list_goods_received->orderBy('required_date')->first()->formulir->form_date,
                'message' => 'please approve purchasing goods received',
                'permission_slug' => 'approval.point.purchasing.goods.received.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_goods_received->get() as $goods_received) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/goods-received/' . $goods_received->id),
                'deadline' => $goods_received->formulir->form_date,
                'message' => 'please approve this purchasing goods received ' . $goods_received->formulir->form_number,
                'permission_slug' => 'approval.point.purchasing.goods.received.fixed.assets'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_goods_received = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();
        // Grouping vesa
        if ($merge_into_group && $list_goods_received->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/goods-received/vesa-rejected'),
                'deadline' => $list_goods_received->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form goods received',
                'permission_slug' => 'update.point.purchasing.goods.received.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_goods_received->get() as $goods_received) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/goods-received/' . $goods_received->id.'/edit'),
                'deadline' => $goods_received->required_date ? : $goods_received->formulir->form_date,
                'message' => $goods_received->formulir->form_number. ' Rejected, please edit your form purchasing goods received',
                'permission_slug' => 'update.point.purchasing.goods.received.fixed.assets'
            ]);
        }

        return $array;
    }
}

<?php

namespace Point\PointPurchasing\Vesa\FixedAssets;

use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisition;

trait FixedAssetsPurchaseOrderVesa
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
        $list_purchase_requisition = FixedAssetsPurchaseRequisition::joinFormulir()
            ->joinEmployee()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_requisition->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-order/vesa-create'),
                'deadline' => $list_purchase_requisition->orderBy('form_date')->first()->form_date,
                'message' => 'please create purchase order fixed assets',
                'permission_slug' => 'create.point.purchasing.order.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_requisition->get() as $purchase_requisition) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-order/create-step-2/' . $purchase_requisition->id),
                'deadline' => $purchase_requisition->required_date ? : $purchase_requisition->formulir->form_date,
                'message' => 'please create this purchase order fixed assets ' . $purchase_requisition->formulir->form_number,
                'permission_slug' => 'create.point.purchasing.order.fixed.assets'
            ]);
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_purchase_order = self::joinFormulir()->open()->notArchived()->approvalPending()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_order->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-order/vesa-approval'),
                'deadline' => $list_purchase_order->orderBy('form_date')->first()->form_date,
                'message' => 'please approve purchase order fixed assets',
                'permission_slug' => 'approval.point.purchasing.order.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_order->get() as $purchase_order) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-order/' . $purchase_order->id),
                'deadline' => $purchase_order->required_date ? : $purchase_order->formulir->form_date,
                'message' => 'please approve this purchase order fixed assets ' . $purchase_order->formulir->form_number,
                'permission_slug' => 'approval.point.purchasing.order.fixed.assets'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_purchase_order = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_order->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-order/vesa-rejected'),
                'deadline' => $list_purchase_order->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.order.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_order->get() as $purchase_order) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-order/' . $purchase_order->id.'/edit'),
                'deadline' => $purchase_order->required_date ? : $purchase_order->formulir->form_date,
                'message' => $purchase_order->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.order.fixed.assets'
            ]);
        }

        return $array;
    }
}

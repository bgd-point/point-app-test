<?php

namespace Point\PointPurchasing\Vesa\Inventory;

use Point\PointPurchasing\Helpers\Inventory\PurchaseOrderHelper;
use Point\PointPurchasing\Helpers\PurchaseRequisitionHelper;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;

trait PurchaseOrderVesa
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
        $list_purchase_requisition = PurchaseRequisitionHelper::availableToOrder();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_requisition->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-order/vesa-create'),
                'deadline' => '', // $list_purchase_requisition->orderBy('required_date')->first()->formulir->form_date,
                'due_date' => '', // (date('Y-m-d 00:00:00') > $list_purchase_requisition->orderBy('required_date')->first()->required_date) ? true : false,
                'message' => 'Please create purchase order',
                'permission_slug' => 'create.point.purchasing.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_requisition as $purchase_requisition) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-order/create-step-2/' . $purchase_requisition->id),
                'deadline' => $purchase_requisition->required_date ? : $purchase_requisition->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $purchase_requisition->required_date) ? true : false,
                'message' => 'Please create purchase order from '. formulir_url($purchase_requisition->formulir),
                'permission_slug' => 'create.point.purchasing.order',
                'color' => ''
            ]);
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_purchase_order = self::joinFormulir()->open()->notArchived()->approvalPending()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-order/vesa-approval'),
                'deadline' => $list_purchase_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve purchase order',
                'permission_slug' => 'approval.point.purchasing.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_order->get() as $purchase_order) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-order/' . $purchase_order->id),
                'deadline' => $purchase_order->formulir->form_date,
                'message' => 'Approval PURCHASE ORDER <b>' . formulir_url($purchase_order->formulir) . '</b> TOTAL <b>' . number_format_price($purchase_order->total) .  '</b> Supplier <b>' . $purchase_order->supplier->name .'</b>',
                'permission_slug' => 'approval.point.purchasing.order'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_purchase_order = self::joinFormulir()->open()->notArchived()->approvalRejected()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-order/vesa-rejected'),
                'deadline' => $list_purchase_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_order->get() as $purchase_order) {
            $url = url('purchasing/point/purchase-order/' . $purchase_order->id.'/edit');
            if ($purchase_order->checkHaveReference() == null) {
                $url = url('purchasing/point/purchase-order/basic/' . $purchase_order->id.'/edit');
            }
            
            array_push($array, [
                'url' => $url,
                'deadline' => $purchase_order->formulir->form_date,
                'message' => $purchase_order->formulir->form_number. ' is rejected by ' . $purchase_order->formulir->createdBy->name . ', please follow up and update your form',
                'permission_slug' => 'update.point.purchasing.order'
            ]);
        }

        return $array;
    }
}

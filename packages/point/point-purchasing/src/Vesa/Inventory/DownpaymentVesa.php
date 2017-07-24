<?php

namespace Point\PointPurchasing\Vesa\Inventory;

use Point\PointPurchasing\Models\Inventory\Downpayment;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;

trait DownpaymentVesa
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

    public static function getVesaCreatePayment()
    {
        return self::vesaCreatePayment([], false);
    }

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_downpayment = Downpayment::joinFormulir()->whereNotNull('purchasing_order_id')->notArchived()->selectOriginal()->get();
        $array_purchase_order_in_downpayment = [];
        foreach ($list_downpayment as $downpayment) {
            array_push($array_purchase_order_in_downpayment, $downpayment->purchasing_order_id);
        }

        $list_purchasing_order = PurchaseOrder::joinFormulir()->approvalApproved()->open()->where('is_cash', true)->whereNotIn('point_purchasing_order.id', $array_purchase_order_in_downpayment)->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_purchasing_order->get()->count() > 5) {
            $purchasing_order = $list_purchasing_order->first();
            array_push($array, [
                'url' => url('purchasing/point/downpayment/vesa-create'),
                'deadline' => $purchasing_order->formulir->form_date,
                'message' => 'Make an purchasing downpayment',
                'permission_slug' => 'create.point.purchasing.downpayment'
            ]);
            return $array;
        }

        // Push all
        foreach ($list_purchasing_order->get() as $purchasing_order) {
            if ($purchasing_order->getTotalRemainingDownpayment($purchasing_order->id) < $purchasing_order->total) {
                array_push($array, [
                    'url' => url('purchasing/point/downpayment/create/'.$purchasing_order->id),
                    'deadline' => $purchasing_order->formulir->form_date,
                    'message' => 'Make an purchasing downpayment from purchasing order ' . $purchasing_order->formulir->form_number,
                    'permission_slug' => 'create.point.purchasing.downpayment'
                ]);
            }
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_downpayment = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_downpayment->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/downpayment/vesa-approval'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve purchasing downpayment',
                'permission_slug' => 'approval.point.purchasing.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('purchasing/point/downpayment/' . $downpayment->id),
                'deadline' => $downpayment->formulir->form_date,
                'message' => 'please approve this purchasing downpayment ' . $downpayment->formulir->form_number,
                'permission_slug' => 'approval.point.purchasing.downpayment'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_downpayment = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_downpayment->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/downpayment/vesa-rejected'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form downpayment',
                'permission_slug' => 'update.point.purchasing.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('purchasing/point/downpayment/' . $downpayment->id.'/edit'),
                'deadline' => $downpayment->formulir->form_date,
                'message' => $downpayment->formulir->form_number. ' Rejected, please edit your form purchasing downpayment',
                'permission_slug' => 'update.point.purchasing.downpayment'
            ]);
        }

        return $array;
    }
}

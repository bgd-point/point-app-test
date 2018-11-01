<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\Sales\SalesOrder;

trait DeliveryOrderVesa
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
        $array_formulir_expedition_locked_by_delivery_order = SalesOrder::getArrayFormulirExpeditionOrderLockedByDeliveryOrder();
        $array_formulir_sales_order_locked_by_expedition = SalesOrder::getArrayFormulirSalesOrderLockedByExpedition();
        $array_expedition_order_locked_by_sales_order = SalesOrder::getArrayExpeditionOrderLockedSalesOrder($array_formulir_sales_order_locked_by_expedition);

        $list_sales_order = SalesOrder::joinFormulir()->with('person')->open()->approvalApproved()->notArchived()->selectOriginal()->orderByStandard();
        if (($array_expedition_order_locked_by_sales_order === $array_formulir_expedition_locked_by_delivery_order) && ($array_formulir_expedition_locked_by_delivery_order)) {
            $list_sales_order = SalesOrder::joinFormulir()->with('person')->open()->approvalApproved()->notArchived()->whereNotIn('formulir_id', $array_formulir_sales_order_locked_by_expedition)->selectOriginal()->orderByStandard();
        }

        // Grouping vesa
        if ($merge_into_group && $list_sales_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/delivery-order/vesa-create'),
                'deadline' => $list_sales_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'you have many sales orders waiting to be delivered',
                'permission_slug' => 'create.point.sales.delivery.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_sales_order->get() as $sales_order) {
            if ($sales_order->is_cash) {
                if ($sales_order->getTotalRemainingDownpayment($sales_order->id) > 0) {
                    array_push($array, [
                        'url' => url('sales/point/indirect/delivery-order/create-step-1/'),
                        'deadline' => $sales_order->formulir->form_date,
                        'message' => 'create delivery order from '
                            . formulir_url($sales_order->formulir)
                            . ' customer <strong>' . $sales_order->person->name . '</strong>',
                        'permission_slug' => 'create.point.sales.delivery.order'
                    ]);
                }
            } else {
                array_push($array, [
                    'url' => url('sales/point/indirect/delivery-order/create-step-1/'),
                    'deadline' => $sales_order->formulir->form_date,
                    'message' => 'create delivery order from '
                        . formulir_url($sales_order->formulir)
                        . ' customer <strong>' . $sales_order->person->name . '</strong>',
                    'permission_slug' => 'create.point.sales.delivery.order'
                ]);
            }
        }

        return $array;
    }
    
    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_delivery_order = self::joinFormulir()->with('person')->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_delivery_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/delivery-order/vesa-approval'),
                'deadline' => $list_delivery_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'you have many delivery orders waiting to be approved',
                'permission_slug' => 'approval.point.sales.delivery.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_delivery_order->get() as $delivery_order) {
            array_push($array, [
                'url' => url('sales/point/indirect/delivery-order/' . $delivery_order->id),
                'deadline' => $delivery_order->formulir->form_date,
                'message' => 'please approve this delivery order '
                    . formulir_url($delivery_order->formulir)
                    . ' customer <strong>' . $delivery_order->person->name . '</strong>',
                'permission_slug' => 'approval.point.sales.delivery.order'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_delivery_order = self::joinFormulir()->with('person')->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_delivery_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/delivery-order/vesa-rejected'),
                'deadline' => $list_delivery_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'you have many rejected delivery orders. please edit your forms',
                'permission_slug' => 'update.point.delivery.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_delivery_order->get() as $delivery_order) {
            array_push($array, [
                'url' => url('sales/point/delivery-order/' . $delivery_order->id.'/edit'),
                'deadline' => $delivery_order->formulir->form_date,
                'message' => 'delivery order '
                    . formulir_url($delivery_order->formulir)
                    . ' customer <strong>' . $delivery_order->person->name . '</strong> rejected, please edit your form',
                'permission_slug' => 'update.point.delivery.order'
            ]);
        }

        return $array;
    }
}

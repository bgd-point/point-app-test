<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\Sales\Downpayment;
use Point\PointSales\Models\Sales\SalesOrder;

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

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_downpayment = Downpayment::joinFormulir()->whereNotNull('sales_order_id')->notArchived()->selectOriginal()->orderByStandard();
        $array_sales_order_in_downpayment = [];
        foreach ($list_downpayment as $downpayment) {
            array_push($array_sales_order_in_downpayment, $downpayment->sales_order_id);
        }

        $list_sales_order = SalesOrder::joinFormulir()->approvalApproved()->open()->where('is_cash', true)->whereNotIn('point_sales_order.id', $array_sales_order_in_downpayment)->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_sales_order->count() > 5) {
            $sales_order = $list_sales_order->first();
            array_push($array, [
                'url' => url('sales/point/indirect/downpayment/vesa-create'),
                'deadline' => $sales_order->formulir->form_date,
                'message' => 'Make an sales downpayment',
                'permission_slug' => 'create.point.sales.downpayment'
            ]);
            return $array;
        }

        // Push all
        foreach ($list_sales_order->get() as $sales_order) {
            if ($sales_order->getTotalRemainingDownpayment($sales_order->id) < $sales_order->total) {
                array_push($array, [
                    'url' => url('sales/point/indirect/downpayment/insert/'.$sales_order->id),
                    'deadline' => $sales_order->formulir->form_date,
                    'message' => 'Make an sales downpayment from sales order ' . formulir_url($sales_order->formulir),
                    'permission_slug' => 'create.point.sales.downpayment'
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
                'url' => url('sales/point/indirect/downpayment/vesa-approval'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve sales downpayment',
                'permission_slug' => 'approval.point.sales.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('sales/point/indirect/downpayment/' . $downpayment->id),
                'deadline' => $downpayment->formulir->form_date,
                'message' => 'please approve this sales downpayment ' . formulir_url($downpayment->formulir),
                'permission_slug' => 'approval.point.sales.downpayment'
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
                'url' => url('sales/point/indirect/downpayment/vesa-rejected'),
                'deadline' => $list_downpayment->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form downpayment',
                'permission_slug' => 'update.point.sales.downpayment'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_downpayment->get() as $downpayment) {
            array_push($array, [
                'url' => url('sales/point/indirect/downpayment/' . $downpayment->id.'/edit'),
                'deadline' => $downpayment->formulir->form_date,
                'message' => formulir_url($downpayment->formulir). ' Rejected, please edit your form sales downpayment',
                'permission_slug' => 'update.point.sales.downpayment'
            ]);
        }

        return $array;
    }
}

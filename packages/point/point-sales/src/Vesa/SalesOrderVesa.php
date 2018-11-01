<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\Sales\SalesQuotation;

trait SalesOrderVesa
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
        $list_sales_quotation = SalesQuotation::joinFormulir()->with('person')->open()->approvalApproved()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_sales_quotation->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/sales-order/vesa-create'),
                'deadline' => $list_sales_quotation->orderBy('required_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_sales_quotation->orderBy('required_date')->first()->required_date) ? true : false,
                'message' => 'you have many sales quotations waiting to be created as sales orders',
                'permission_slug' => 'create.point.sales.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_sales_quotation->get() as $sales_quotation) {
            array_push($array, [
                'url' => url('sales/point/indirect/sales-order/create-step-2/' . $sales_quotation->id),
                'deadline' => $sales_quotation->required_date,
                'due_date' => (date('Y-m-d 00:00:00') > $sales_quotation->required_date) ? true : false,
                'message' => 'create sales order from '
                    . formulir_url($sales_quotation->formulir)
                    . ' customer <strong>' . $sales_quotation->person->name . '</strong>',
                'permission_slug' => 'create.point.sales.order'
            ]);
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_sales_order = self::joinFormulir()->with('person')->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_sales_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/sales-order/vesa-approval'),
                'deadline' => $list_sales_order->orderBy('form_date')->first()->required_date,
                'message' => 'you have many sales orders waiting to be approved',
                'permission_slug' => 'approval.point.sales.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_sales_order->get() as $sales_order) {
            array_push($array, [
                'url' => url('sales/point/indirect/sales-order/' . $sales_order->id),
                'deadline' => $sales_order->formulir->form_date,
                'message' => 'please approve this sales order '
                    . formulir_url($sales_order->formulir)
                    . ' customer <strong>' . $sales_order->person->name . '</strong> amount '
                    . number_format_price($sales_order->total),
                'permission_slug' => 'approval.point.sales.order'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_sales_order = self::joinFormulir()->with('person')->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_sales_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/sales-order/vesa-rejected'),
                'deadline' => $list_sales_order->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Sales orders rejected, please edit your forms',
                'permission_slug' => 'update.point.sales.order'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_sales_order->get() as $sales_order) {
            $url = url('sales/point/indirect/sales-order/' . $sales_order->id.'/edit');
            if ($sales_order->checkHaveReference() == null) {
                $url = url('sales/point/indirect/sales-order/basic/' . $sales_order->id.'/edit');
            }
            array_push($array, [
                'url' => $url,
                'deadline' => $sales_order->formulir->form_date,
                'message' => 'sales order '
                    . $sales_order->formulir->form_number
                    . ' customer <strong>'. $sales_order->person->name . '</strong> Rejected, please edit your form',
                'permission_slug' => 'update.point.sales.order'
            ]);
        }

        return $array;
    }
}

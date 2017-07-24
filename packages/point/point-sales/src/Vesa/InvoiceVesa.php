<?php

namespace Point\PointSales\Vesa;

use Point\PointSales\Models\Sales\DeliveryOrder;

trait InvoiceVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreate();
        return $array;
    }

    public static function getVesaCreate()
    {
        return self::vesaCreate([], false);
    }
    
    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_delivery_order = DeliveryOrder::joinFormulir()->availableToInvoiceGroupCustomer()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_delivery_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/invoice/vesa-create'),
                'deadline' => $list_delivery_order->orderBy('form_date')->first()->form_date,
                'message' => 'Make an sales invoice',
                'permission_slug' => 'create.point.sales.invoice'
            ]);
            return $array;
        }

        // Push all
        foreach ($list_delivery_order->get() as $delivery_order) {
            array_push($array, [
                'url' => url('sales/point/indirect/invoice/create-step-2/'.$delivery_order->person_id),
                'deadline' => $delivery_order->formulir->form_date,
                'message' => 'Make an sales invoice from supplier ' . $delivery_order->person->codeName,
                'permission_slug' => 'create.point.sales.invoice'
            ]);
        }

        return $array;
    }
}

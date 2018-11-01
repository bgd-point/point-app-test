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
        $list_delivery_order = DeliveryOrder::joinFormulir()->with('person')->availableToInvoiceGroupCustomer()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_delivery_order->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/invoice/vesa-create'),
                'deadline' => $list_delivery_order->orderBy('form_date')->first()->form_date,
                'message' => 'you have many delivery orders waiting to get invoices',
                'permission_slug' => 'create.point.sales.invoice'
            ]);
            return $array;
        }

        // Push all
        foreach ($list_delivery_order->get() as $delivery_order) {
            array_push($array, [
                'url' => url('sales/point/indirect/invoice/create-step-2/'.$delivery_order->person_id),
                'deadline' => $delivery_order->formulir->form_date,
                'message' => 'create sales invoice from delivery order '
                    . formulir_url($delivery_order->formulir)
                    . ' customer <strong>' . $delivery_order->person->name . '</strong>',
                'permission_slug' => 'create.point.sales.invoice'
            ]);
        }

        return $array;
    }
}

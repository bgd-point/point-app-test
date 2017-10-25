<?php

namespace Point\PointSales\Helpers;

use Point\PointSales\Models\Service\InvoiceService;

class ServiceReportHelper
{
    /**
     * Helper for Report Service by Value
     */

    public static function detailByService($service_id, $date_from, $date_to)
    {
        $data = InvoiceService::getDetail($service_id, $date_from, $date_to)
            ->selectRaw('sum(point_sales_service_invoice_service.quantity) as quantity, sum(point_sales_service_invoice_service.price) as price')
            ->first();

        if ($data) {
            return $data;
        }

        return null;
    }

    public static function getDetailByService($service_id, $date_from, $date_to)
    {
        $data = InvoiceService::getDetail($service_id, $date_from, $date_to)
            ->select('point_sales_service_invoice_service.*');

        return $data;
    }
}

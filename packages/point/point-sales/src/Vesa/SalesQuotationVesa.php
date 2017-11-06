<?php

namespace Point\PointSales\Vesa;

trait SalesQuotationVesa
{
    public static function getVesa()
    {
        $array = self::vesaApproval();
        $array = self::vesaReject($array);

        return $array;
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaReject()
    {
        return self::vesaReject([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_sales_quotation = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_sales_quotation->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/indirect/sales-quotation/vesa-approval'),
                'deadline' => $list_sales_quotation->orderBy('required_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_sales_quotation->orderBy('required_date')->first()->required_date) ? true : false,
                'message' => 'please approve sales quotation',
                'permission_slug' => 'approval.point.sales.quotation'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_sales_quotation->get() as $sales_quotation) {
            array_push($array, [
                'url' => url('sales/point/indirect/sales-quotation/' . $sales_quotation->id),
                'deadline' => $sales_quotation->required_date,
                'due_date' => (date('Y-m-d 00:00:00') > $sales_quotation->required_date) ? true : false,
                'message' => 'please approve this sales quotation ' . formulir_url($sales_quotation->formulir),
                'permission_slug' => 'approval.point.sales.quotation'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_sales_quotation = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_sales_quotation->get()->count() > 5) {
            array_push($array, [
                'url' => url('sales/point/sales-quotation/vesa-rejected'),
                'deadline' => $list_sales_quotation->orderBy('required_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_sales_quotation->orderBy('required_date')->first()->required_date) ? true : false,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.sales.quotation'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_sales_quotation->get() as $sales_quotation) {
            array_push($array, [
                'url' => url('sales/point/sales-quotation/' . $sales_quotation->id.'/edit'),
                'deadline' => $sales_quotation->required_date ? : $sales_quotation->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $sales_quotation->required_date) ? true : false,
                'message' => $sales_quotation->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.sales.quotation'
            ]);
        }

        return $array;
    }
}

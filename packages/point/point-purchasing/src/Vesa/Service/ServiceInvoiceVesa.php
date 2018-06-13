<?php

namespace Point\PointPurchasing\Vesa\Service;

use Point\PointPurchasing\Models\Service\Invoice;

trait ServiceInvoiceVesa
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
        $list_invoice = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_invoice->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/service/invoice/vesa-approval'),
                'deadline' => $list_invoice->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve purchasing invoice',
                'permission_slug' => 'approval.point.purchasing.service.invoice'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('purchasing/point/service/invoice/' . $invoice->id),
                'deadline' => $invoice->formulir->form_date,
                'message' => 'please approve this purchasing invoice ' . formulir_url($invoice->formulir),
                'permission_slug' => 'approval.point.purchasing.service.invoice'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_invoice = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_invoice->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/service/invoice/vesa-rejected'),
                'deadline' => $list_invoice->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form invoice',
                'permission_slug' => 'update.point.purchasing.service.invoice'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_invoice->get() as $invoice) {
            array_push($array, [
                'url' => url('purchasing/point/service/invoice/' . $invoice->id.'/edit'),
                'deadline' => $invoice->formulir->form_date,
                'message' => formulir_url($invoice->formulir) . ' Rejected, please edit your form purchasing invoice',
                'permission_slug' => 'update.point.purchasing.service.invoice'
            ]);
        }

        return $array;
    }
}

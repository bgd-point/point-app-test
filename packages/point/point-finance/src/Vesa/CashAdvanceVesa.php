<?php

namespace Point\PointFinance\Vesa;

trait CashAdvanceVesa
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

    public static function getVesaCreatePayment()
    {
        return self::vesaCreatePayment([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_cash_advance = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_cash_advance->count() > 5) {
            array_push($array, [
                'url' => url('finance/point/cash-advance/vesa-approval'),
                'deadline' => $list_cash_advance->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve finance cash advance',
                'permission_slug' => 'approval.point.finance.cash.advance'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cash_advance->get() as $cash_advance) {
            array_push($array, [
                'url' => url('finance/point/cash-advance/' . $cash_advance->id),
                'deadline' => $cash_advance->formulir->form_date,
                'message' => 'please approve this finance cash advance ' . $cash_advance->formulir->form_number,
                'permission_slug' => 'approval.point.finance.cash.advance'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_cash_advance = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal()->orderByStandard();
        // Grouping vesa
        if ($merge_into_group && $list_cash_advance->count() > 5) {
            array_push($array, [
                'url' => url('finance/point/cash-advance/vesa-rejected'),
                'deadline' => $list_cash_advance->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form cash advance',
                'permission_slug' => 'update.point.finance.cash.advance'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cash_advance->get() as $cash_advance) {
            array_push($array, [
                'url' => url('finance/point/cash-advance/' . $cash_advance->id.'/edit'),
                'deadline' => $cash_advance->formulir->form_date,
                'message' => $cash_advance->formulir->form_number. ' Rejected, please edit your form finance cash advance',
                'permission_slug' => 'update.point.finance.cash.advance'
            ]);
        }

        return $array;
    }
}

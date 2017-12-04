<?php

namespace Point\PointPurchasing\Vesa\Inventory;

use Point\PointPurchasing\Models\Inventory\CashAdvance;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;

trait CashAdvanceVesa
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
        $list_cash_advance = CashAdvance::joinFormulir()->notArchived()->approvalApproved()->select('purchase_requisition_id')->get()->toArray();
        $list_purchasing_requisition = PurchaseRequisition::joinFormulir()
            ->approvalApproved()
            ->open()
            ->notArchived()
            ->where('include_cash_advance', true)
            ->whereNotIn('point_purchasing_requisition.id', $list_cash_advance)
            ->selectOriginal()
            ->orderByStandard()
            ->get();

        // Grouping vesa
        if ($merge_into_group && $list_purchasing_requisition->count() > 5) {
            $purchasing_requisition = $list_purchasing_requisition->first();
            array_push($array, [
                'url' => url('purchasing/point/cash-advance/vesa-create'),
                'deadline' => $purchasing_requisition->formulir->form_date,
                'message' => 'Make an purchasing cash advance',
                'permission_slug' => 'create.point.purchasing.cash.advance'
            ]);
            return $array;
        }

        // Push all
        foreach ($list_purchasing_requisition as $purchasing_requisition) {
            array_push($array, [
                'url' => url('purchasing/point/cash-advance/create/'.$purchasing_requisition->id),
                'deadline' => $purchasing_requisition->formulir->form_date,
                'message' => 'Make an purchasing cash advance from purchasing requisition ' . $purchasing_requisition->formulir->form_number,
                'permission_slug' => 'create.point.purchasing.cash.advance'
            ]);
        }

        return $array;
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_cash_advance = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_cash_advance->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/cash-advance/vesa-approval'),
                'deadline' => $list_cash_advance->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve purchasing cash advance',
                'permission_slug' => 'approval.point.purchasing.cash.advance'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cash_advance->get() as $cash_advance) {
            array_push($array, [
                'url' => url('purchasing/point/cash-advance/' . $cash_advance->id),
                'deadline' => $cash_advance->formulir->form_date,
                'message' => 'please approve this purchasing cash advance ' . formulir_url($cash_advance->formulir),
                'permission_slug' => 'approval.point.purchasing.cash.advance'
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
                'url' => url('purchasing/point/cash-advance/vesa-rejected'),
                'deadline' => $list_cash_advance->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'Rejected, please edit your form cash advance',
                'permission_slug' => 'update.point.purchasing.cash.advance'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cash_advance->get() as $cash_advance) {
            array_push($array, [
                'url' => url('purchasing/point/cash-advance/' . $cash_advance->id.'/edit'),
                'deadline' => $cash_advance->formulir->form_date,
                'message' => formulir_url($cash_advance->formulir) . ' Rejected, please edit your form purchasing cash advance',
                'permission_slug' => 'update.point.purchasing.cash.advance'
            ]);
        }

        return $array;
    }
}

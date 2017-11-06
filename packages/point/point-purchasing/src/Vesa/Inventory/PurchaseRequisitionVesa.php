<?php

namespace Point\PointPurchasing\Vesa\Inventory;

trait PurchaseRequisitionVesa
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
        $list_purchase_requisition = self::joinFormulir()->open()->notArchived()->approvalPending()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_requisition->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-requisition/vesa-approval'),
                'deadline' => $list_purchase_requisition->orderBy('required_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_purchase_requisition->orderBy('required_date')->first()->required_date) ? true : false,
                'message' => 'please approve purchase requisition',
                'permission_slug' => 'approval.point.purchasing.requisition'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_requisition->get() as $purchase_requisition) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-requisition/' . $purchase_requisition->id),
                'deadline' => $purchase_requisition->required_date ? : $purchase_requisition->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $purchase_requisition->required_date) ? true : false,
                'message' => 'please approve this purchase requisition ' . formulir_url($purchase_requisition->formulir),
                'permission_slug' => 'approval.point.purchasing.requisition'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_purchase_requisition = self::joinFormulir()->open()->notArchived()->approvalRejected()->selectOriginal()->orderByStandard();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_requisition->get()->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-requisition/vesa-rejected'),
                'deadline' => $list_purchase_requisition->orderBy('required_date')->first()->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $list_purchase_requisition->orderBy('required_date')->first()->required_date) ? true : false,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.requisition'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_requisition->get() as $purchase_requisition) {
            array_push($array, [
                'url' => url('purchasing/point/purchase-requisition/' . $purchase_requisition->id.'/edit'),
                'deadline' => $purchase_requisition->required_date ? : $purchase_requisition->formulir->form_date,
                'due_date' => (date('Y-m-d 00:00:00') > $purchase_requisition->required_date) ? true : false,
                'message' => formulir_url($purchase_requisition->formulir) . ' Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.requisition'
            ]);
        }

        return $array;
    }
}

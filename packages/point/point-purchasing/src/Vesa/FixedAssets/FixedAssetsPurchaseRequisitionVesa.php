<?php

namespace Point\PointPurchasing\Vesa\FixedAssets;

use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisition;

trait FixedAssetsPurchaseRequisitionVesa
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
        $list_purchase_requisition = self::joinFormulir()->open()->notArchived()->approvalPending()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_requisition->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-requisition/vesa-approval'),
                'deadline' => $list_purchase_requisition->orderBy('form_date')->first()->form_date,
                'message' => 'please approve purchase requisition fixed assets',
                'permission_slug' => 'approval.point.purchasing.requisition.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_requisition->get() as $purchase_requisition) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-requisition/' . $purchase_requisition->id),
                'deadline' => $purchase_requisition->required_date ? : $purchase_requisition->formulir->form_date,
                'message' => 'please approve this purchase requisition fixed assets ' . $purchase_requisition->formulir->form_number,
                'permission_slug' => 'approval.point.purchasing.requisition.fixed.assets'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_purchase_requisition = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_purchase_requisition->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-requisition/vesa-rejected'),
                'deadline' => $list_purchase_requisition->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.requisition.fixed.assets'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_purchase_requisition->get() as $purchase_requisition) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/purchase-requisition/' . $purchase_requisition->id.'/edit'),
                'deadline' => $purchase_requisition->required_date ? : $purchase_requisition->formulir->form_date,
                'message' => $purchase_requisition->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.purchasing.requisition.fixed.assets'
            ]);
        }

        return $array;
    }
}

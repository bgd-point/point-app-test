<?php

namespace Point\PointInventory\Vesa;

trait TransferItemVesa
{
    public static function getVesa()
    {
        $array = self::vesaApproval();
        $array = self::VesaReject($array);
        $array = self::VesaReceive($array);
        return $array;
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaReject()
    {
        return self::VesaReject([], false);
    }

    public static function getVesaReceive()
    {
        return self::VesaReceive([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_transfer_item = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_transfer_item->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/transfer-item/send/vesa-approval'),
                'deadline' => $list_transfer_item->orderBy('form_date')->first()->formulir->form_date,
                'message' => 'please approve this transfer item',
                'permission_slug' => 'approval.point.inventory.transfer.item'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_transfer_item->get() as $transfer_item) {
            array_push($array, [
                'url' => url('inventory/point/transfer-item/send/' . $transfer_item->id),
                'deadline' => $transfer_item->formulir->form_date,
                'message' => 'please approve this transfer item ' . $transfer_item->formulir->form_number,
                'permission_slug' => 'approval.point.inventory.transfer.item'
            ]);
        }

        return $array;
    }

    private static function VesaReject($array = [], $merge_into_group = true)
    {
        $list_transfer_item = self::joinFormulir()->open()->approvalRejected()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_transfer_item->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/transfer-item/send/vesa-rejected'),
                'deadline' => $list_transfer_item->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.transfer.item'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_transfer_item->get() as $transfer_item) {
            array_push($array, [
                'url' => url('inventory/point/transfer-item/send/' . $transfer_item->id.'/edit'),
                'deadline' => $transfer_item->formulir->form_date,
                'message' => $transfer_item->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.inventory.transfer.item'
            ]);
        }

        return $array;
    }

    private static function vesaReceive($array = [], $merge_into_group = true)
    {
        $list_transfer_receive_item = self::joinFormulir()->open()->approvalApproved()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_transfer_receive_item->count() > 5) {
            array_push($array, [
                'url' => url('inventory/point/transfer-item/received/vesa-receive-item'),
                'deadline' => $list_transfer_receive_item->orderBy('form_date')->first()->form_date,
                'message' => 'receive item',
                'permission_slug' => 'create.point.inventory.transfer.item'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_transfer_receive_item->get() as $transfer_receive_item) {
            array_push($array, [
                'url' => url('inventory/point/transfer-item/received/create/' . $transfer_receive_item->id),
                'deadline' => $transfer_receive_item->formulir->form_date,
                'message' => 'receive item from ' . $transfer_receive_item->formulir->form_number,
                'permission_slug' => 'create.point.inventory.transfer.item'
            ]);
        }

        return $array;
    }
}

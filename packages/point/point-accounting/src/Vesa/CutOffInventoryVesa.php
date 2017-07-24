<?php

namespace Point\PointAccounting\Vesa;

use Point\PointAccounting\Models\CutOffInventory;

trait CutOffInventoryVesa {

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
        $list_cut_off = self::joinFormulir()->notArchived()->open()->approvalPending()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_cut_off->count() > 5) {
            array_push($array, [
                'url' => url('accounting/point/cut-off/inventory/vesa-approval'),
                'deadline' => $list_cut_off->orderBy('form_date')->first()->form_date,
                'message' => 'please approve cut off inventory',
                'permission_slug' => 'approval.point.accounting.cut.off.inventory'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cut_off->get() as $memo_journal) {
            array_push($array, [
                'url' => url('accounting/point/cut-off/inventory/' . $memo_journal->id),
                'deadline' => $memo_journal->required_date ? : $memo_journal->formulir->form_date,
                'message' => 'please approve this cut off inventory ' . $memo_journal->formulir->form_number,
                'permission_slug' => 'approval.point.accounting.cut.off.inventory'
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_cut_off = self::joinFormulir()->open()->approvalRejected()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_cut_off->count() > 5) {
            array_push($array, [
                'url' => url('accounting/point/cut-off/inventory/vesa-rejected'),
                'deadline' => $list_cut_off->orderBy('form_date')->first()->form_date,
                'message' => 'Rejected, please edit your form',
                'permission_slug' => 'update.point.accounting.cut.off.inventory'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cut_off->get() as $memo_journal) {
            array_push($array, [
                'url' => url('accounting/point/cut-off/inventory/' . $memo_journal->id.'/edit'),
                'deadline' => $memo_journal->required_date ? : $memo_journal->formulir->form_date,
                'message' => $memo_journal->formulir->form_number. ' Rejected, please edit your form',
                'permission_slug' => 'update.point.accounting.cut.off.inventory'
            ]);
        }

        return $array;
    }

}

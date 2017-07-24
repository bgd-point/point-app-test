<?php

namespace Point\PointPurchasing\Vesa\FixedAssets;

use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\FixedAssetsContract;
use Point\Framework\Models\Master\FixedAssetsContractDetail;
use Point\Framework\Models\Master\FixedAssetsContractReference;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;

trait FixedAssetsInvoiceVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreate();
        $array = self::vesaCreateContract($array);

        return $array;
    }

    public static function getVesaCreate()
    {
        return self::vesaCreate([], false);
    }

    public static function getVesaCreateContract()
    {
        return self::vesaCreateContract([], false);
    }

    private static function vesaCreate($array = [], $merge_into_group = true)
    {
        $list_goods_received = FixedAssetsGoodsReceived::joinFormulir()->availableToInvoiceGroupSupplier()->selectOriginal();
        // Grouping vesa
        if ($merge_into_group && $list_goods_received->count() > 5) {
            foreach ($list_goods_received->get() as $goods_received) {
                array_push($array, [
                    'url' => url('purchasing/point/fixed-assets/invoice/vesa-create'),
                    'deadline' => $goods_received->orderBy('required_date')->first()->required_date,
                    'message' => 'Make an purchasing invoice',
                    'permission_slug' => 'create.point.purchasing.invoice.fixed.assets'
                ]);
            }
            return $array;
        }

        // Push all
        foreach ($list_goods_received->get() as $goods_received) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/invoice/create-step-2/'.$goods_received->supplier_id),
                'deadline' => $goods_received->required_date ? : $goods_received->formulir->form_date,
                'message' => 'Make an purchasing invoice from goods received ' . $goods_received->formulir->form_number,
                'permission_slug' => 'create.point.purchasing.invoice.fixed.assets'
            ]);
        }

        return $array;
    }

    private static function vesaCreateContract($array=[], $merge_into_group = true)
    {
        $list_contract_reference = FixedAssetsContractReference::whereNull('fixed_assets_contract_id');

        // Grouping vesa
        if ($merge_into_group && $list_contract_reference->count() > 5) {
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/contract/list-create'),
                'deadline' => $list_contract_reference->orderBy('journal.form_date', 'desc')->first()->form_date,
                'message' => 'received new fixed assets',
                'permission_slug' => 'create.point.purchasing.contract'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_contract_reference->get() as $contract_reference) {
            $position = JournalHelper::position($contract_reference->coa_id);
            array_push($array, [
                'url' => url('purchasing/point/fixed-assets/contract/create-step-2/' . $contract_reference->id),
                'deadline' => $contract_reference->journal->form_date,
                'message' => 'please create activa contract from formulir number '.$contract_reference->formulir->form_number .' with amount ' .number_format_accounting($contract_reference->journal->$position),
                'permission_slug' => 'create.point.purchasing.contract'
            ]);
        }

        return $array;
    }
}

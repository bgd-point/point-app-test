<?php

namespace Point\PointPurchasing\Helpers\FixedAssets;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\FixedAssetsContract;
use Point\Framework\Models\Master\FixedAssetsContractDetail;
use Point\Framework\Models\Master\FixedAssetsContractReference;

class FixedAssetsContractHelper
{
    public static function searchList($list_contract, $date_from, $date_to, $search)
    {
        if ($date_from) {
            $list_contract = $list_contract->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_contract = $list_contract->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_contract = $list_contract->where(function ($q) use ($search) {
                $q->where('fixed_assets_contract.name', 'like', '%' . $search . '%')
                    ->orWhere('formulir.form_number', 'like', '%' . $search . '%');
            });
        }

        return $list_contract;
    }

    public static function create(Request $request, $formulir)
    {
        $journal = Journal::find($request->input('journal_id'));
        if (!$journal) {
            throw new PointException("JOURNAL NOT FOUND");
        }

        $contract = new FixedAssetsContract();
        $contract->formulir_id = $formulir->id;
        $contract->journal_id = $request->input('journal_id');
        $contract->coa_id = $request->input('coa_id');
        $contract->date_purchased = date_format_db($request->input('purchase_date'));
        $contract->supplier_id = $request->input('supplier_id');
        $contract->code = self::getLastCode();
        $contract->name = $request->input('name');
        $contract->unit = $request->input('unit');
        $contract->useful_life = number_format_db($request->input('useful_life'));
        $contract->salvage_value = $request->input('salvage_value') ? number_format_db($request->input('salvage_value')) : 0;
        $contract->total_paid = $request->input('total_paid') ? number_format_db($request->input('total_paid')) : 0;
        $contract->depreciation = number_format_db($request->input('depreciation'));
        $contract->quantity = number_format_db($request->input('quantity'));
        $contract->price = number_format_db($request->input('price'));
        $contract->total_price = number_format_db($request->input('total_price'));
        $contract->save();
        for ($i=0; $i < count($request->input('fixed_assets_contract_reference_id')); $i++) {
            $contract_detail = new FixedAssetsContractDetail();
            $contract_detail->contract_id = $contract->id;
            $contract_detail->fixed_assets_contract_reference_id = $request->input('fixed_assets_contract_reference_id')[$i];
            $contract_detail->save();

            $contract_reference = FixedAssetsContractReference::find($contract_detail->fixed_assets_contract_reference_id);
            $contract_reference->fixed_assets_contract_id =  $contract->id;
            $contract_reference->save();
        }
        

        return $contract;
    }

    public static function getLastCode()
    {
        $last_fixed_assets = FixedAssetsContract::orderBy('id', 'desc')->first();
        $new_code = 1;
        if ($last_fixed_assets) {
            $new_code = (int)str_replace('FA-', '', $last_fixed_assets->code);
            $new_code += 1;
        }

        return 'FA-' . ($new_code);
    }

    public static function join($request)
    {
        $contract = FixedAssetsContract::find($request->input('contract_id'));
        $contract_detail = new FixedAssetsContractDetail();
        $contract_detail->contract_id = $request->input('contract_id');
        $contract_detail->fixed_assets_contract_reference_id = $request->input('contract_reference_id');
        $contract_detail->save();

        $contract_reference = FixedAssetsContractReference::find($request->input('contract_reference_id'));
        $contract_reference->fixed_assets_contract_id =  $contract->id;
        $contract_reference->save();
        
        // Update contract
        $contract->total_price = $contract->total_price + $contract_reference->total_price;
        $contract->save();

        return $contract;
    }

    public static function updateReference($contract_id)
    {
        $list_contract_reference = FixedAssetsContractReference::where('fixed_assets_contract_id', $contract_id)->get();
        foreach ($list_contract_reference as $contract_reference) {
            $contract_reference->fixed_assets_contract_id = null;
            $contract_reference->save();
        }

        return true;
    }
}

<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDepreciation extends Model
{
    protected $table = 'account_depreciation';
    public $timestamps = false;

    public static function exist($account_fixed_asset_id, $account_depreciation_id)
    {
        if (AccountDepreciation::where('account_fixed_asset_id', '=', $account_fixed_asset_id)
            ->where('account_depreciation_id', '=', $account_depreciation_id)->first()) {
            return true;
        }

        return false;
    }

    public static function insert($account_fixed_asset_id, $account_depreciation_id)
    {
        if (self::exist($account_fixed_asset_id, $account_depreciation_id)) {
            return false;
        }

        $account_depreciation = AccountDepreciation::where('account_fixed_asset_id', $account_fixed_asset_id)->first();
        if (! $account_depreciation) {
            $account_depreciation = new AccountDepreciation;
        }

        $account_depreciation->account_fixed_asset_id = $account_fixed_asset_id;
        $account_depreciation->account_depreciation_id = $account_depreciation_id;
        $account_depreciation->save();

        return $account_depreciation;
    }

    public function coaFixedAsset()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'account_fixed_asset_id');
    }

    public function coaDepreciation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'account_depreciation_id');
    }

    public static function getDepreciation($depreciation_id)
    {
        $account_depreciation =  AccountDepreciation::where('account_depreciation_id', '=', $depreciation_id)->first();
        if (! $account_depreciation) {
            return 0;
        }

        return $account_depreciation->account_fixed_asset_id;
    }
}

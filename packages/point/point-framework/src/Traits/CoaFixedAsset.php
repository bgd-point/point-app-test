<?php

namespace Point\Framework\Traits;

use Point\Framework\Models\FixedAsset;

trait CoaFixedAsset
{
    public function getSubledgerName()
    {
        $explode = explode("\\", $this->subledger_type);

        if (! $explode) {
            return '-';
        }

        return $explode[count($explode) - 1];
    }

    public function getUsefulLife()
    {
        $fixed_asset = FixedAsset::where('account_id', $this->id)->first();

        if (! $fixed_asset) {
            return 0;
        }

        return $fixed_asset->useful_life;
    }

    public function isFixedAssetAccount()
    {
        if ($this->category->name == 'Fixed Assets' && $this->has_subledger == 1) {
            return true;
        }

        return false;
    }

    public function isDepreciationAccount()
    {
        if ($this->category->name == 'Fixed Assets' && $this->has_subledger == 0) {
            return true;
        }

        return false;
    }
}

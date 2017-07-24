<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;

class FixedAssetsContractDetail extends Model
{
    protected $table = 'fixed_assets_contract_detail';
    public $timestamps = false;
    
    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function journal()
    {
        return $this->belongsTo('\Point\Framework\Models\Journal', 'journal_id');
    }
    
    public function reference()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\FixedAssetsContractReference', 'fixed_assets_contract_reference_id');
    }
}

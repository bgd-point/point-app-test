<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class FixedAssetsPurchaseRequisitionDetail extends Model
{
    use ByTrait;

    protected $table = 'point_purchasing_fixed_assets_requisition_detail';
    public $timestamps = false;

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }
}

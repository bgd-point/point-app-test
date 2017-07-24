<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;
use Point\PointPurchasing\Vesa\FixedAssets\FixedAssetsGoodsReceivedVesa;

class FixedAssetsGoodsReceived extends Model
{
    use ByTrait, FormulirTrait, FixedAssetsGoodsReceivedVesa;

    protected $table = 'point_purchasing_fixed_assets_goods_received';
    public $timestamps = false;

    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_goods_received.supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder', 'fixed_assets_order_id');
    }
    
    public function details()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceivedDetail', 'fixed_assets_goods_received_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function scopeAvailableToInvoiceGroupSupplier($q)
    {
        $q->open()->approvalApproved()->notArchived()->orderByStandard()->groupBy('point_purchasing_fixed_assets_goods_received.supplier_id');
    }

    public function scopeAvailableToInvoice($q, $supplier_id)
    {
        $q->open()->approvalApproved()->notArchived()->where('point_purchasing_fixed_assets_goods_received.supplier_id', '=', $supplier_id)->orderByStandard();
    }

    public function checkReference()
    {
        $goods_received = FixedAssetsGoodsReceived::find($this->id);
        $reference = FormulirHelper::getLockedModel($goods_received->formulir_id);
        
        return $reference;
    }

    public function checkReferenceExpedition($reference)
    {
        if (get_class($reference) == 'Point\PointExpedition\Models\ExpeditionOrder') {
            $reference_purchase = FormulirHelper::getLockedModel($reference->formulir_id);
            return $reference_purchase;
        }

        return null;
    }
}

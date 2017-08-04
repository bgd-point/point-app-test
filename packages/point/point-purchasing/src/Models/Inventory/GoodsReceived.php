<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\FormulirTrait;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Vesa\Inventory\GoodsReceivedVesa;

class GoodsReceived extends Model
{
    use ByTrait, FormulirTrait, GoodsReceivedVesa;

    protected $table = 'point_purchasing_goods_received';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_goods_received.supplier_id');
    }
    
    public function purchaseOrder()
    {
        return $this->belongsTo('\Point\PointPurchasing\Models\Inventory\PurchaseOrder', 'point_purchasing_order_id');
    }
    
    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\GoodsReceivedItem', 'point_purchasing_goods_received_id');
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
        $q->open()->approvalApproved()->notArchived()->orderByStandard()->groupBy('point_purchasing_goods_received.supplier_id');
    }

    public function scopeAvailableToInvoice($q, $supplier_id)
    {
        $q->open()->approvalApproved()->notArchived()->where('point_purchasing_goods_received.supplier_id', '=', $supplier_id)->orderByStandard();
    }

    public function checkReference()
    {
        $goods_received = GoodsReceived::find($this->id);
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

    public static function showUrl($id)
    {
        $class = self::find($id);
        if ($class->formulir->form_number) {
            return '/purchasing/point/goods-received/'.$id;
        }

        return '/purchasing/point/goods-received/'.$id.'/archived';
    }
}

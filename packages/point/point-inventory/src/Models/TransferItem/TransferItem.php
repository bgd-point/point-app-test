<?php

namespace Point\PointInventory\Models\TransferItem;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointInventory\Vesa\TransferItemVesa;
use Point\PointInventory\Vesa\TransferItemReceiveVesa;

class TransferItem extends Model
{
    protected $table = 'point_inventory_transfer_item';
    public $timestamps = false;

    use FormulirTrait, TransferItemVesa;

    /**
     * Inject function when saving
     *
     * @param array $options
     *
     * @return bool|null
     */
    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }

    public function scopeJoinDependencies($q)
    {
        $q->joinFormulir()->notArchived()->notCanceled()->selectOriginal();
    }

    public function warehouseFrom()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Warehouse', 'warehouse_sender_id', 'id');
    }

    public function warehouseTo()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Warehouse', 'warehouse_receiver_id', 'id');
    }

    public function items()
    {
        return $this->hasMany('Point\PointInventory\Models\TransferItem\TransferItemDetail', 'transfer_item_id', 'id');
    }

    public static function bladeEmail()
    {
        return 'point-expedition::emails.expedition.point.approval.downpayment';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/inventory/point/transfer-item/send/' . $id;
        }

        return '/inventory/point/transfer-item/send/' . $id . '/archived';
    }
}

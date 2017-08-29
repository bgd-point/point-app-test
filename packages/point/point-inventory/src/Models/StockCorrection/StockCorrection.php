<?php

namespace Point\PointInventory\Models\StockCorrection;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointInventory\Vesa\StockCorrectionVesa;

class StockCorrection extends Model
{
    protected $table = 'point_inventory_stock_correction';
    public $timestamps = false;

    use FormulirTrait, StockCorrectionVesa;

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

    public function items()
    {
        return $this->hasMany('Point\PointInventory\Models\StockCorrection\StockCorrectionItem', 'point_inventory_stock_correction_id', 'id');
    }

    public function scopeJoinWarehouse($q)
    {
        $q->join('warehouse', 'warehouse.id', '=', $this->table.'.warehouse_id');
    }
    
    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public static function bladeEmail()
    {
        return 'point-inventory::emails.inventory.point.approval.stock-correction';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/inventory/point/stock-correction/' . $id;
        }

        return '/inventory/point/stock-correction/' . $id . '/archived';
    }
}

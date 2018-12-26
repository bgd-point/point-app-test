<?php

namespace Point\PointInventory\Models\StockOpname;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointInventory\Vesa\StockOpnameVesa;

class StockOpname extends Model
{
    protected $table = 'point_inventory_stock_opname';
    public $timestamps = false;

    use FormulirTrait, StockOpnameVesa;

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
        return $this->hasMany('Point\PointInventory\Models\StockOpname\StockOpnameItem', 'stock_opname_id', 'id');
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
        return 'point-inventory::emails.inventory.point.approval.stock-opname-email';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/inventory/point/stock-opname/' . $id;
        }

        return '/inventory/point/stock-opname/' . $id . '/archived';
    }
}

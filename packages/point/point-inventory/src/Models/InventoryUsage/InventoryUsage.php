<?php

namespace Point\PointInventory\Models\InventoryUsage;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;
use Point\PointInventory\Vesa\InventoryUsageVesa;

class InventoryUsage extends Model
{
    protected $table = 'point_inventory_usage';
    public $timestamps = false;

    use FormulirTrait , InventoryUsageVesa;

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

    public function listInventoryUsage()
    {
        return $this->hasMany('Point\PointInventory\Models\InventoryUsage\InventoryUsageItem', 'inventory_usage_id', 'id');
    }

    public function scopeJoinWarehouse($q)
    {
        $q->join('warehouse', 'warehouse.id', '=', $this->table.'.warehouse_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function employee()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'employee_id');
    }

    public static function bladeEmail()
    {
        return 'point-inventory::emails.inventory.point.approval.inventory-usage-email';
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/inventory/point/inventory-usage/' . $id;
        }

        return '/inventory/point/inventory-usage/' . $id . '/archived';
    }
}

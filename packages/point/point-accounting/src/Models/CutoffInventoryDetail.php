<?php

namespace Point\PointAccounting\Models;

use Illuminate\Database\Eloquent\Model;

class CutOffInventoryDetail extends Model
{
    protected $table = 'point_accounting_cut_off_inventory_detail';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Item','subledger_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Warehouse','warehouse_id');
    }

    public function scopeJoinInventory($q)
    {
        $q->join('point_accounting_cut_off_inventory', 'point_accounting_cut_off_inventory.id', '=', $this->table.'.cut_off_inventory_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_accounting_cut_off_inventory.formulir_id');
    }
}

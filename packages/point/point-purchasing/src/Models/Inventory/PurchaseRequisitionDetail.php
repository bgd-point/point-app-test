<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class PurchaseRequisitionDetail extends Model
{
    use ByTrait;

    protected $table = 'point_purchasing_requisition_item';
    public $timestamps = false;

    public function scopeJoinAllocation($q){
        $q->join('allocation', 'allocation.id', '=', 'point_purchasing_requisition_item.allocation_id');
    }
    public function scopeJoinItem($q){
        $q->join('item', 'item.id', '=', 'point_purchasing_requisition_item.item_id');
    }
    //join from detail purchase to header purchase
    public function scopeJoinPurchasingRequisition($q){
        $q->join('point_purchasing_requisition', 'point_purchasing_requisition.id', '=', 'point_purchasing_requisition_item.point_purchasing_requisition_id');
    }

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_requisition.supplier_id');
    }
    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'point_purchasing_requisition.formulir_id');
    }

}

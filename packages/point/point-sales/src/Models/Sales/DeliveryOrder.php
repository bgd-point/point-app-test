<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Traits\FormulirTrait;
use Point\PointSales\Models\Sales\DeliveryOrderItem;
use Point\PointSales\Vesa\DeliveryOrderVesa;

class DeliveryOrder extends Model
{
    use ByTrait, FormulirTrait, DeliveryOrderVesa;

    protected $table = 'point_sales_delivery_order';
    public $timestamps = false;

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
    
    public function scopeJoinPerson($q)
    {
        $q->join('person', 'person.id', '=', 'point_sales_delivery_order.person_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo('\Point\PointSales\Models\Sales\SalesOrder', 'point_sales_order_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointSales\Models\Sales\DeliveryOrderItem', 'point_sales_delivery_order_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Warehouse', 'warehouse_id');
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function scopeJoinDeliveryOrder($q, $sales_order_id)
    {
        $q->join('point_sales_delivery_order_item', $this->table .'.id', '=', 'point_sales_delivery_order_id')
            ->where($this->table .'.point_sales_order_id', '=', $sales_order_id);
    }

    public function scopeAvailableToInvoiceGroupCustomer($q)
    {
        $array_delivery_order_id_open = [] ;
        $list_delivery_order = DeliveryOrder::joinFormulir()->notArchived()->approvalApproved()->selectOriginal()->get();
        foreach ($list_delivery_order as $delivery_order) {
            $is_locked_form_delivery_order = FormulirHelper::isLocked($delivery_order->formulir_id);
            if ($is_locked_form_delivery_order) {
                continue;
            }

            array_push($array_delivery_order_id_open, $delivery_order->id);
        }

        $q->open()->approvalApproved()->notArchived()->orderByStandard()->whereIn('point_sales_delivery_order.id', $array_delivery_order_id_open)->groupBy('point_sales_delivery_order.person_id');
    }

    public function scopeAvailableToInvoice($q, $person_id)
    {
        $q->open()
            ->approvalApproved()
            ->whereNull('formulir.archived')
            ->where('point_sales_delivery_order.person_id', '=', $person_id)
            ->orderByStandard();
    }

    public function getTotalGoodsSent($delivery_order_id)
    {
        $amount = 0;
        $list_delivery_items = DeliveryOrderItem::where('point_sales_delivery_order_id', $delivery_order_id)->get();
        foreach ($list_delivery_items as $item) {
            $per_row = $item->quantity * $item->price;
            $amount = $amount + $per_row;
        }
        
        return $amount;
    }

    public function checkReference()
    {
        $delivery_order = DeliveryOrder::find($this->id);
        $reference = FormulirHelper::getLockedModel($delivery_order->formulir_id);
        
        return $reference;
    }

    public function checkReferenceExpedition($reference)
    {
        if (get_class($reference) == 'Point\PointExpedition\Models\ExpeditionOrder') {
            $reference_sales = FormulirHelper::getLockedModel($reference->formulir_id);
            return $reference_sales;
        }

        return null;
    }

    public static function showUrl()
    {
        if ($this->formulir->form_number) {
            return '/sales/point/indirect/delivery-oder/'.$this->$id;
        } else {
            return '/sales/point/indirect/delivery-oder/'.$this->$id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-sales::app.emails.sales.point.approval.delivery-order';
    }
}

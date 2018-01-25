<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\SalesQuotation;
use Point\PointSales\Vesa\SalesOrderVesa;

class SalesOrder extends Model
{
    protected $table = 'point_sales_order';
    public $timestamps = false;

    use ByTrait, FormulirTrait, SalesOrderVesa;

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
        $q->join('person', 'person.id', '=', 'point_sales_order.person_id');
    }

    public function scopeIncludeExpedition($q)
    {
        $q->joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('include_expedition', '=', 1)
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeExcludeExpedition($q)
    {
        $array_formulir_expedition_locked_by_delivery_order = self::getArrayFormulirExpeditionOrderLockedByDeliveryOrder();
        $array_formulir_sales_order_locked_by_expedition = self::getArrayFormulirSalesOrderLockedByExpedition();
        $array_expedition_order_locked_by_sales_order = self::getArrayExpeditionOrderLockedSalesOrder($array_formulir_sales_order_locked_by_expedition);

        if (($array_expedition_order_locked_by_sales_order === $array_formulir_expedition_locked_by_delivery_order) && ($array_formulir_expedition_locked_by_delivery_order)) {
            $q->joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('include_expedition', '=', 0)
            ->whereNotIn('formulir_id', $array_formulir_sales_order_locked_by_expedition)
            ->selectOriginal()
            ->orderByStandard();
        } else {
            $q->joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('include_expedition', '=', 0)
            ->selectOriginal()
            ->orderByStandard();
        }
    }

    public function items()
    {
        return $this->hasMany('\Point\PointSales\Models\Sales\SalesOrderItem', 'point_sales_order_id');
    }

    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/sales/point/indirect/sales-order/'.$class->id;
        } else {
            return '/sales/point/indirect/sales-order/'.$class->id.'/archived';
        }
    }

    public function getTotalDownpayment($sales_order_id)
    {
        $downpayment_amount = 0;
        $list_downpayment = Downpayment::joinFormulir()->close()->where('sales_order_id', $sales_order_id)->get();
        foreach ($list_downpayment as $downpayment) {
            $downpayment_amount += $downpayment->amount;
        }
        
        return $downpayment_amount;
    }

    public function getTotalRemainingDownpayment($sales_order_id)
    {
        $downpayment_remaining = 0;
        $list_downpayment = Downpayment::joinFormulir()->close()->where('sales_order_id', $sales_order_id)->get();
        foreach ($list_downpayment as $downpayment) {
            $downpayment_remaining += ReferHelper::remaining(get_class($downpayment), $downpayment->formulirable_id, $downpayment->amount);
        }
        
        return $downpayment_remaining;
    }

    public function checkDownpayment()
    {
        $downpayment_remaining = self::getTotalRemainingDownpayment($this->id);
        if ($downpayment_remaining < $this->total) {
            echo '<a href="'.url('sales/point/indirect/downpayment/insert/'. $this->id).'" class="btn btn-effect-ripple btn-xs btn-info" style="overflow: hidden; position: relative;"><i class="fa fa-external-link"></i> Downpayment</a>';
        } else {
            echo '<label style="font-size:12px; border-left:5px #45a7b9 solid; padding:5px">Downpayment Has been paid with amount <br><strong style="font-size:16px">'.number_format_price($downpayment_remaining).'</strong> </label> ';
        }
    }

    public function getTotalGoodsSent($sales_order_id)
    {
        $amount = 0;
        $list_delivery_order = DeliveryOrder::joinFormulir()->notArchived()->approvalApproved()->where('point_sales_order_id', $sales_order_id)->get();
        
        if (! count($list_delivery_order) > 0) {
            return 0;
        }
        
        foreach ($list_delivery_order as $delivery_order) {
            $total_per_row = $delivery_order->getTotalGoodsSent($delivery_order->formulirable_id);
            $amount = $amount + $total_per_row;
        }

        return $amount;
    }

    public function createDeliveryFromExpedition()
    {
        if ($this->is_cash) {
            if (! self::getTotalRemainingDownpayment($this->id) > 0) {
                self::checkDownpayment();
                return false;
            }
        }

        if ($this->include_expedition) {
            return false;
        }

        // get expedition order have not used in delivery
        $array_expedition_order_locked = self::getArrayFormulirExpeditionOrderLockedByDeliveryOrder();
        $locking_expedition = FormulirLock::where('locked_id', $this->formulir_id)
            ->whereNotIn('locking_id', $array_expedition_order_locked)
            ->orderBy('id', 'DESC')
            ->get();

        $array_expedition_order_open = [];
        foreach ($locking_expedition as $expedition_order) {
            $formulir = Formulir::find($expedition_order->locking_id);
            if ($formulir->form_number != null) {
                array_push($array_expedition_order_open, $formulir->id);
            }
        }

        if (! $array_expedition_order_open) {
            return false;
        }
        $expedition = ExpeditionOrder::where('formulir_id', $array_expedition_order_open[0])->first();
        if ($locking_expedition) {
            echo '<a href="'.url('sales/point/indirect/delivery-order/create-step-2/'.$this->id.'/'.$expedition->id).'"
               class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i> Create delivery order</a>';
            return true;
        }

        return false;
    }

    /**
     * For get formulir id expedition order already locked by delivery order
     * @return Array formulir_id
     */
    public static function getArrayFormulirExpeditionOrderLockedByDeliveryOrder()
    {
        // checking expedition order already locked by delivery order
        $array_expedition_order_locked = [];
        $list_delivery_order = DeliveryOrder::where('include_expedition', 0)->get();
        foreach ($list_delivery_order as $delivery_order) {
            $expedition_locked = FormulirLock::where('locking_id', $delivery_order->formulir_id)
                ->orderBy('id', 'DESC')
                ->first();
            array_push($array_expedition_order_locked, $expedition_locked->locked_id);
        }

        return $array_expedition_order_locked;
    }

    public static function getArrayFormulirSalesOrderLockedByExpedition()
    {
        $array_expedition_order_locked = self::getArrayFormulirExpeditionOrderLockedByDeliveryOrder();

        $sales_order_id_is_locked = [];
        $sales_order_locked_by_expedition = FormulirLock::whereIn('locking_id', $array_expedition_order_locked)->get();
        foreach ($sales_order_locked_by_expedition as $sales_locked_id) {
            array_push($sales_order_id_is_locked, $sales_locked_id->locked_id);
        }

        if (! count($sales_order_id_is_locked) > 0) {
            return null;
        }
        
        $new_sales_order_id_is_locked = [];
        for ($i=0; $i < count($sales_order_id_is_locked); $i++) {
            $check_form_is_sales_order = Formulir::where('id', $sales_order_id_is_locked[$i])->where('formulirable_type', get_class(new SalesOrder))->first();
            
            if ($check_form_is_sales_order) {
                array_push($new_sales_order_id_is_locked, $check_form_is_sales_order->id);
            }
        }

        return $new_sales_order_id_is_locked;
    }

    public static function getArrayExpeditionOrderLockedSalesOrder($formulir_sales_order)
    {
        $array_expedition_order_is_locked_by_sales = [];
        $formulir_expedition_locked = FormulirLock::whereIn('locked_id', $formulir_sales_order)->get();
        foreach ($formulir_expedition_locked as $formulir_id) {
            $check_form_is_expedition_order = Formulir::where('id', $formulir_id->locking_id)->where('formulirable_type', get_class(new ExpeditionOrder))->first();
            if ($check_form_is_expedition_order) {
                array_push($array_expedition_order_is_locked_by_sales, $check_form_is_expedition_order->id);
            }
        }

        return $array_expedition_order_is_locked_by_sales;
    }

    /**
     * Checking is sales order have reference from sales quotation
     * return sales quotation formulir id
     */
    public function checkHaveReference()
    {
        $sales_quotation = null;
        $formulir_lock = FormulirLock::where('locking_id', $this->formulir_id)->first();
        if ($formulir_lock) {
            $sales_quotation = SalesQuotation::where('formulir_id', $formulir_lock->locked_id)->first();
        }

        return $sales_quotation;
    }

    public static function bladeEmail()
    {
        return 'point-sales::app.emails.sales.point.approval.sales-order';
    }
}

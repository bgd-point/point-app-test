<?php

namespace Point\PointPurchasing\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointPurchasing\Models\Inventory\Downpayment;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;
use Point\PointPurchasing\Vesa\Inventory\PurchaseOrderVesa;

class PurchaseOrder extends Model
{
    use ByTrait, FormulirTrait, PurchaseOrderVesa;

    protected $table = 'point_purchasing_order';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_order.supplier_id');
    }

    public function scopeIncludeExpedition($q, $supplier_id)
    {
        $q->joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('include_expedition', '=', 1)
            ->where('supplier_id', $supplier_id)
            ->selectOriginal()
            ->orderByStandard();
    }

    public function scopeExcludeExpedition($q, $supplier_id)
    {
        $purchase_order = $q->joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('include_expedition', '=', 0)
            ->where('supplier_id', $supplier_id)
            ->select('formulir_id')
            ->orderByStandard();

        if (! $purchase_order) {
            return false;
        }

        $purchase_order = $purchase_order->get()->toArray();
        $formulir_lock = FormulirLock::join('formulir', 'formulir_lock.locking_id', '=', 'formulir.id' )
            ->whereIn('locked_id', $purchase_order)
            ->where('formulirable_type', get_class(new ExpeditionOrder()))
            ->whereNotNull('form_number')
            ->where('form_status', '!=', -1)
            ->where('approval_status', 1)
            ->where('locked', true)
            ->groupBy('locking_id')
            ->select('locking_id')
            ->get()
            ->toArray();

        $expedition_order = ExpeditionOrder::whereIn('formulir_id', $formulir_lock)->groupBy('form_reference_id')->paginate(100);
        $reference_id = ExpeditionOrder::whereIn('formulir_id', $formulir_lock)->groupBy('form_reference_id')->select('form_reference_id')->get()->toArray();
        return [
            'expedition_order' =>$expedition_order,
            'reference_id' => $reference_id
        ];
    }

    public function items()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\Inventory\PurchaseOrderItem', 'point_purchasing_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public static function showUrl($id)
    {
        $purchase_order = PurchaseOrder::find($id);
        if ($purchase_order->formulir->form_number) {
            return '/purchasing/point/purchase-order/'.$purchase_order->id;
        } else {
            return '/purchasing/point/purchase-order/'.$purchase_order->id.'/archived';
        }
    }

    public function checkHaveReference()
    {
        $purchase_requisition = null;
        $formulir_lock = FormulirLock::where('locking_id', $this->formulir_id)->first();
        if ($formulir_lock) {
            $purchase_requisition = PurchaseRequisition::where('formulir_id', $formulir_lock->locked_id)->first();
        }

        return $purchase_requisition;
    }

    public function checkExpeditionReference($formulir_id)
    {
        $expedition_order = ExpeditionOrder::joinFormulir()->approvalApproved()->notArchived()->where('done', 0)->where('form_reference_id', $formulir_id)->orderBy('group')->selectOriginal()->first();
        if ($expedition_order) {
            return true;
        }
        return false;
    }

    public function getTotalDownpayment($purchasing_order_id)
    {
        $downpayment_amount = 0;
        $list_downpayment = Downpayment::joinFormulir()->close()->where('purchasing_order_id', $purchasing_order_id)->get();
        foreach ($list_downpayment as $downpayment) {
            $downpayment_amount += $downpayment->amount;
        }
        
        return $downpayment_amount;
    }

    public function getTotalRemainingDownpayment($purchasing_order_id)
    {
        $downpayment_remaining = 0;
        $list_downpayment = Downpayment::joinFormulir()->close()->where('purchasing_order_id', $purchasing_order_id)->get();
        foreach ($list_downpayment as $downpayment) {
            $downpayment_remaining += ReferHelper::remaining(get_class($downpayment), $downpayment->formulirable_id, $downpayment->amount);
        }
        
        return $downpayment_remaining;
    }

    public function checkDownpayment()
    {
        $downpayment_remaining = self::getTotalRemainingDownpayment($this->id);
        if ($downpayment_remaining < $this->total) {
            echo '<a href="'.url('purchasing/point/downpayment/create/'. $this->id).'" class="btn btn-effect-ripple btn-xs btn-info" style="overflow: hidden; position: relative;"><i class="fa fa-external-link"></i> Downpayment</a>';
        } else {
            echo '<label style="font-size:12px; border-left:5px #45a7b9 solid; padding:5px">Downpayment Has been paid with amount <br><strong style="font-size:16px">'.number_format_price($downpayment_remaining).'</strong> </label> ';
        }
    }

    public function getTotalGoodsReceived($purchasing_order_id)
    {
        $amount = 0;
        $list_goods_received = GoodsReceived::joinFormulir()->notArchived()->approvalApproved()->where('point_purchasing_order_id', $purchasing_order_id)->get();
        
        if (! count($list_goods_received) > 0) {
            return 0;
        }
        
        foreach ($list_goods_received as $delivery_order) {
            $total_per_row = $delivery_order->getTotalGoodsReceived($delivery_order->formulirable_id);
            $amount = $amount + $total_per_row;
        }

        return $amount;
    }


    public function createGoodsReceiveFromExpedition()
    {
        if ($this->is_cash) {
            if (! self::getTotalRemainingDownpayment($this->id) > 0) {
                return false;
            }    
        }
        
        if ($this->include_expedition) {
            return false;
        }

        // get expedition order have not used in delivery
        $array_expedition_order_locked = self::getArrayFormulirExpeditionOrderLockedByGoodsReceived();
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
            echo '<a href="'.url('purchasing/point/goods-received/create-step-2/'.$this->id.'/'.$expedition->id).'"
               class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i> Create Good Received</a>';
        }

        return false;
    }

    /**
     * For get formulir id expedition order already locked by delivery order
     * @return Array formulir_id
     */
    public static function getArrayFormulirExpeditionOrderLockedByGoodsReceived()
    {
        // checking expedition order already locked by delivery order
        $array_expedition_order_locked = [];
        $list_delivery_order = GoodsReceived::where('include_expedition', 0)->get();
        foreach ($list_delivery_order as $delivery_order) {
            $expedition_locked = FormulirLock::where('locking_id', $delivery_order->formulir_id)
                ->orderBy('id', 'DESC')
                ->first();
            array_push($array_expedition_order_locked, $expedition_locked->locked_id);
        }

        return $array_expedition_order_locked;
    }

    public static function getArrayFormulirPurchaseOrderLockedByExpedition()
    {
        $array_expedition_order_locked = self::getArrayFormulirExpeditionOrderLockedByGoodsReceived();

        $purchase_order_id_is_locked = [];
        $purchase_order_locked_by_expedition = FormulirLock::whereIn('locking_id', $array_expedition_order_locked)->get();
        foreach ($purchase_order_locked_by_expedition as $purchase_locked_id) {
            array_push($purchase_order_id_is_locked, $purchase_locked_id->locked_id);
        }

        if (! count($purchase_order_id_is_locked) > 0) {
            return null;
        }
        
        $new_purchase_order_id_is_locked = [];
        for ($i=0; $i < count($purchase_order_id_is_locked); $i++) {
            $check_form_is_purchase_order = Formulir::where('id', $purchase_order_id_is_locked[$i])->where('formulirable_type', get_class(new PurchaseOrder))->first();
            
            if ($check_form_is_purchase_order) {
                array_push($new_purchase_order_id_is_locked, $check_form_is_purchase_order->id);
            }
        }

        return $new_purchase_order_id_is_locked;
    }

    public static function getArrayExpeditionOrderLockedPurchaseOrder($formulir_purchase_order)
    {
        $array_expedition_order_is_locked_by_purchase = [];
        $formulir_expedition_locked = FormulirLock::whereIn('locked_id', $formulir_purchase_order)->get();
        foreach ($formulir_expedition_locked as $formulir_id) {
            $check_form_is_expedition_order = Formulir::where('form_status', '!=', -1)->where('id', $formulir_id->locking_id)->where('formulirable_type', get_class(new ExpeditionOrder))->first();
            if ($check_form_is_expedition_order) {
                array_push($array_expedition_order_is_locked_by_purchase, $check_form_is_expedition_order->id);
            }
        }

        return $array_expedition_order_is_locked_by_purchase;
    }

    /**
     * For get invoice data when create payment order in purchasing and this function using in payment order purchasing.
     */

    public function getPurchaseInvoice()
    {
        $formulir_lock = FormulirLock::join('formulir', 'formulir.id', '=', 'formulir_lock.locking_id')
            ->where('formulir_lock.locked', 1)
            ->where('formulir.form_status', '!=', -1)
            ->where('formulir.formulirable_type', '=', get_class(new Invoice))
            ->select('formulir.*')
            ->first();
        if (!$formulir_lock) {
            return null;
        }

        return $formulir_lock->formulirable_type::find($formulir_lock->formulirable_id);

    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.purchase-order';
    }
}

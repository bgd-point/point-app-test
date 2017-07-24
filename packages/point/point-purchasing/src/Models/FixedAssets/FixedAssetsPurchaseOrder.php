<?php

namespace Point\PointPurchasing\Models\FixedAssets;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Traits\FormulirTrait;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsDownpayment;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisition;
use Point\PointPurchasing\Vesa\FixedAssets\FixedAssetsPurchaseOrderVesa;

class FixedAssetsPurchaseOrder extends Model
{
    use ByTrait, FormulirTrait, FixedAssetsPurchaseOrderVesa;

    protected $table = 'point_purchasing_fixed_assets_order';
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
        $q->join('person', 'person.id', '=', 'point_purchasing_fixed_assets_order.supplier_id');
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
        $array_formulir_expedition_locked_by_delivery_order = self::getArrayFormulirExpeditionOrderLockedByGoodsReceived();
        $array_formulir_purchase_order_locked_by_expedition = self::getArrayFormulirPurchaseOrderLockedByExpedition();
        $array_expedition_order_locked_by_purchase_order = self::getArrayExpeditionOrderLockedPurchaseOrder($array_formulir_purchase_order_locked_by_expedition);

        if (($array_expedition_order_locked_by_purchase_order === $array_formulir_expedition_locked_by_delivery_order) && ($array_formulir_expedition_locked_by_delivery_order)) {
            $q->joinFormulir()
            ->notArchived()
            ->open()
            ->approvalApproved()
            ->where('include_expedition', '=', 0)
            ->whereNotIn('formulir_id', $array_formulir_purchase_order_locked_by_expedition)
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

    public function details()
    {
        return $this->hasMany('\Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrderDetail', 'fixed_assets_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public static function showUrl($id)
    {
        $purchase_order = FixedAssetsPurchaseOrder::find($id);

        if ($purchase_order->formulir->form_number) {
            return '/purchasing/point/fixed-assets/purchase-order/'.$id;
        } else {
            return '/purchasing/point/fixed-assets/purchase-order/'.$id.'/archived';
        }
    }

    public function checkHaveReference()
    {
        $purchase_requisition = null;
        $formulir_lock = FormulirLock::where('locking_id', $this->formulir_id)->first();
        if ($formulir_lock) {
            $purchase_requisition = FixedAssetsPurchaseRequisition::where('formulir_id', $formulir_lock->locked_id)->first();
        }

        return $purchase_requisition;
    }


    public function getTotalDownpayment($purchasing_order_id)
    {
        $downpayment_amount = 0;
        $list_downpayment = FixedAssetsDownpayment::joinFormulir()->close()->where('fixed_assets_order_id', $purchasing_order_id)->get();
        foreach ($list_downpayment as $downpayment) {
            $downpayment_amount += $downpayment->amount;
        }
        
        return $downpayment_amount;
    }

    public function getTotalRemainingDownpayment($purchasing_order_id)
    {
        $downpayment_remaining = 0;
        $list_downpayment = FixedAssetsDownpayment::joinFormulir()->close()->where('fixed_assets_order_id', $purchasing_order_id)->get();
        foreach ($list_downpayment as $downpayment) {
            $downpayment_remaining += ReferHelper::remaining(get_class($downpayment), $downpayment->formulirable_id, $downpayment->amount);
        }
        
        return $downpayment_remaining;
    }

    public function checkDownpayment()
    {
        $downpayment_remaining = self::getTotalRemainingDownpayment($this->id);
        if ($downpayment_remaining < $this->total) {
            echo '<a href="'.url('purchasing/point/fixed-assets/downpayment/create/'. $this->id).'" class="btn btn-effect-ripple btn-xs btn-info" style="overflow: hidden; position: relative;"><i class="fa fa-external-link"></i> Downpayment</a>';
        } else {
            echo '<label style="font-size:12px; border-left:5px #45a7b9 solid; padding:5px">Downpayment Has been paid with amount <br><strong style="font-size:16px">'.number_format_price($downpayment_remaining).'</strong> </label> ';
        }
    }

    public function getTotalGoodsReceived($purchasing_order_id)
    {
        $amount = 0;
        $list_goods_received = FixedAssetsGoodsReceived::joinFormulir()->notArchived()->approvalApproved()->where('fixed_assets_order_id', $purchasing_order_id)->get();
        
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
        if (! self::getTotalRemainingDownpayment($this->id) > 0) {
            self::checkDownpayment();
            return false;
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
            echo '<a href="'.url('purchasing/point/fixed-assets/goods-received/create-step-2/'.$this->id.'/'.$expedition->id).'"
               class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i> Create Good Received</a>';
            return true;
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
        $list_delivery_order = FixedAssetsGoodsReceived::where('include_expedition', 0)->get();
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
            $check_form_is_expedition_order = Formulir::where('id', $formulir_id->locking_id)->where('formulirable_type', get_class(new ExpeditionOrder))->first();
            if ($check_form_is_expedition_order) {
                array_push($array_expedition_order_is_locked_by_purchase, $check_form_is_expedition_order->id);
            }
        }

        return $array_expedition_order_is_locked_by_purchase;
    }

    public static function bladeEmail()
    {
        return 'point-purchasing::emails.purchasing.point.approval.fixed-assets.purchase-order';
    }
}

<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class PurchaseOrder extends Model {
    use ByTrait, FormulirTrait;

    /**
     * @var string
     */
    protected $table = 'point_purchasing_service_purchase_order';
    /**
     * @var mixed
     */
    public $timestamps = false;

    /**
     * Inject function when saving
     *
     *
     * @param  array       $options
     * @return bool|null
     */
    public function save(array $options = []) {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id   = $this->id;
        $this->formulir->save();

        return $this;
    }

    /**
     * @return mixed
     */
    public function person() {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public function scopeJoinSupplier($q)
    {
        $q->join('person', 'person.id', '=', 'point_purchasing_service_purchase_order.person_id');
    }

    /**
     * @return mixed
     */
    public function services() {
        return $this->hasMany('\Point\PointPurchasing\Models\Service\PurchaseOrderDetail', 'purchase_order_id');
    }

    /**
     * @param $id
     */
    public static function showUrl($id) {
        $class = self::find($id);
        if ($class->formulir->form_number) {
            return '/purchasing/point/service/purchase-order/' . $id;
        } else {
            return '/purchasing/point/service/purchase-order/' . $id . '/archived';
        }
    }

    public static function bladeEmail() {
        return 'point-purchasing::emails.purchasing.point.approval.service-purchase-order';
    }
}

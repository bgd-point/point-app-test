<?php

namespace Point\PointPurchasing\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class PurchaseOrderDetail extends Model {
  use ByTrait, FormulirTrait;

  /**
   * @var string
   */
  protected $table = 'point_purchasing_service_purchase_order_detail';
  /**
   * @var mixed
   */
  public $timestamps = false;

  /**
   * @var array
   */
  protected $guarded = [];

  /**
   * @return mixed
   */
  public function service() {
    return $this->belongsTo('\Point\Framework\Models\Master\Service', 'service_id');
  }

  /**
   * @return mixed
   */
  public function allocation() {
    return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
  }
}

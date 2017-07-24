<?php

namespace Point\PointFinance\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReferenceDetail extends Model
{
    protected $table = 'point_finance_payment_reference_detail';
    public $timestamps = false;

    public function save(array $options = [])
    {
        if ($this->allocation_id == null || $this->allocation_id == 0) {
            $this->allocation_id = 1;
        }

        return parent::save();
    }

    public function coa()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function allocation()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public function formReference()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'form_reference_id');
    }
}

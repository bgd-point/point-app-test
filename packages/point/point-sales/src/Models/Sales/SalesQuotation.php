<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;
use Point\PointSales\Vesa\SalesQuotationVesa;

class SalesQuotation extends Model
{
    protected $table = 'point_sales_quotation';
    public $timestamps = false;

    use ByTrait, FormulirTrait, SalesQuotationVesa;

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
        $q->join('person', 'person.id', '=', $this->table.'.person_id');
    }

    public function lockedBy()
    {
        return $this->hasMany('\Point\Framework\Models\FormulirLock', 'locked_id', 'formulir_id');
    }

    public function items()
    {
        return $this->hasMany('\Point\PointSales\Models\Sales\SalesQuotationItem', 'point_sales_quotation_id');
    }
    
    public function person()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'person_id');
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/sales/point/indirect/sales-quotation/'.$class->id;
        } else {
            return '/sales/point/indirect/sales-quotation/'.$class->id.'/archived';
        }
    }

    public static function bladeEmail()
    {
        return 'point-sales::app.emails.sales.point.approval.sales-quotation';
    }
}

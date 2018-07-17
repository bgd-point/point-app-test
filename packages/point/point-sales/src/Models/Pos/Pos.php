<?php

namespace Point\PointSales\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class Pos extends Model
{
    use ByTrait, FormulirTrait;

    protected $table = 'point_sales_pos';
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

    public function scopeJoinDependencies($q)
    {
        $q->joinFormulir()
            ->joinCustomer()
            ->joinDetailItem()
            ->joinItem()
            ->notArchived()
            ->groupBy('point_sales_pos.id')
            ->selectOriginal()
            ->orderBy('point_sales_pos.id', 'desc');
    }

    public function scopeJoinDetailItem($q)
    {
        $q->join('point_sales_pos_item', 'point_sales_pos_item.pos_id', '=', $this->table.'.id');
    }

    public function scopeJoinItem($q)
    {
        $q->join('item', 'item.id', '=', 'point_sales_pos_item.item_id');
    }

    public function scopeJoinCustomer($q)
    {
        $q->join('person', 'person.id', '=', $this->table.'.customer_id');
    }

    public function scopeShowToday($q)
    {
        $q->whereDate('formulir.form_date', '=', date('Y-m-d'));
    }

    public function scopeUserChasier($q)
    {
        $q->where('formulir.created_by', auth()->user()->id);
    }

    public function scopeOrderByDate($q)
    {
        $q->orderBy(DB::raw('formulir.form_date', 'desc'));
    }

    public function items()
    {
        return $this->hasMany('Point\PointSales\Models\Pos\PosItem', 'pos_id');
    }

    public function customer()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\User', 'created_by');
    }

    public function retur()
    {
        return $this->hasOne('\Point\PointSales\Models\Pos\PosRetur', 'pos_id');
    }

    public static function showUrl($id)
    {
        $class = self::find($id);

        if ($class->formulir->form_number) {
            return '/sales/point/pos/'.$class->id;
        } else {
            return '/sales/point/pos/'.$class->id.'/archived';
        }
    }
}

<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\MasterTrait;
use Point\Framework\Traits\FormulirTrait;

class FixedAssetsContract extends Model
{
    protected $table = 'fixed_assets_contract';
    public $timestamps = false;
    use HistoryTrait, ByTrait, FormulirTrait, MasterTrait;

    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }
    
    public function scopeSearch($q, $search)
    {
        $array_of_search = explode(' ', $search);

        foreach ($array_of_search as $search) {
            $q->where(function ($query) use ($search) {
                $query->where('fixed_assets_item.name', 'like', '%'.$search.'%')
                    ->orWhere('fixed_assets_item.code', 'like', '%'.$search.'%');
            });
        }

        return $q;
    }

    public function details()
    {
        return $this->hasMany('\Point\Framework\Models\Master\FixedAssetsContractDetail', 'contract_id');
    }

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    /**
     * @return string
     */
    public function getCodeNameAttribute()
    {
        return '['.$this->attributes['code'] . '] ' . $this->attributes['name'];
    }
}

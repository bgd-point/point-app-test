<?php 

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class CoaGroupCategory extends Model
{
    protected $table = 'coa_group_category';
    public $timestamps = false;

    use HistoryTrait, ByTrait;

    public static function exist($name)
    {
        if (CoaGroupCategory::where('name', '=', $name)->first()) {
            return true;
        }

        return false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function position()
    {
        return $this->belongsTo('Point\Framework\Models\Master\CoaPosition', 'coa_position_id');
    }

    /**
     * @return mixed
     */
    public function category()
    {
        return $this->hasMany('Point\Framework\Models\Master\CoaCategory', 'coa_group_category_id')->orderBy('coa_number', 'asc');
    }

    /**
     * @return mixed
     */
    public function coa()
    {
        if ((request()->get('database_name') == 'p_test' || request()->get('database_name') == 'p_personalfinance') && auth()->user()->name != 'lioni') {
            dd('gc');
            return $this->hasMany('Point\Framework\Models\Master\Coa', 'coa_group_id')
                ->where('coa.name', 'not like', '%lioni%')
                ->orderBy('coa_number', 'asc');
        } else {
            return $this->hasMany('Point\Framework\Models\Master\Coa', 'coa_group_id')->orderBy('coa_number', 'asc');
        }
    }

    public function getAccountAttribute()
    {
        return $this->attributes['coa_number'] . ' ' . $this->attributes['name'];
    }

    public function getParentNumberAttribute()
    {
        return substr($this->attributes['coa_number'], 0, 2);
    }
}

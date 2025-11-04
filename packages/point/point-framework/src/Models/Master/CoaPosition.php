<?php 

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class CoaPosition extends Model
{
    protected $table = 'coa_position';
    public $timestamps = false;

    use HistoryTrait, ByTrait;

    public static function exist($name)
    {
        if (CoaPosition::where('name', '=', $name)->first()) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function groupCategory()
    {
        return $this->hasMany('Point\Framework\Models\Master\CoaGroupCategory', 'coa_position_id')->orderBy('coa_number', 'asc');
    }

    /**
     * @return mixed
     */
    public function category()
    {
        return $this->hasMany('Point\Framework\Models\Master\CoaCategory', 'coa_position_id')->orderBy('coa_number', 'asc');
    }

    /**
     * @return mixed
     */
    public function coa()
    {
        return $this->hasMany('Point\Framework\Models\Master\Coa', 'coa_group_id')->orderBy('coa_number', 'asc');
    }

    /**
     * @return string
     */
    public function getAccountAttribute()
    {
        return $this->attributes['coa_number'] . ' ' . $this->attributes['name'];
    }
}

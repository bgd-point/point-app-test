<?php 

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class CoaCategory extends Model
{
    protected $table = 'coa_category';
    public $timestamps = false;

    use HistoryTrait, ByTrait;

    public static function insert($coa_position_id, $coa_group_category_id, $name)
    {
        $coa_category = CoaCategory::where('name', '=', $name)
            ->where('coa_position_id', '=', $coa_position_id)
            ->where('coa_group_category_id', '=', $coa_group_category_id)
            ->first();
        if (! $coa_category) {
            $coa_category = new CoaCategory;
            $coa_category->coa_position_id = $coa_position_id;
            $coa_category->coa_group_category_id = $coa_group_category_id;
            $coa_category->name = $name;
            $coa_category->save();
        }

        return $coa_category;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groupCategory()
    {
        return $this->belongsTo('Point\Framework\Models\Master\CoaGroupCategory', 'coa_group_category_id');
    }

    /**
     * @return mixed
     */
    public function group()
    {
        return $this->hasMany('Point\Framework\Models\Master\CoaGroup', 'coa_category_id')->orderBy('coa_number', 'asc');
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
    public function coa()
    {
        return $this->hasMany('Point\Framework\Models\Master\Coa', 'coa_category_id')->orderBy('coa_number', 'asc');
    }

    public function coaWithoutGroup()
    {
        return $this->hasMany('Point\Framework\Models\Master\Coa', 'coa_category_id')->where('coa_group_id', null)->orderBy('coa_number', 'asc');
    }

    /**
     * @return string
     */
    public function getAccountAttribute()
    {
        return $this->attributes['coa_number'] . ' ' . $this->attributes['name'];
    }
}

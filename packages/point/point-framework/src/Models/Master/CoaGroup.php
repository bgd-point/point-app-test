<?php 

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;

class CoaGroup extends Model
{
    protected $table = 'coa_group';
    
    use HistoryTrait, ByTrait;

    public static function insert($coa_category_id, $name, $notes = null)
    {
        $coa_group = CoaCategory::where('name', '=', $name)
            ->first();

        if (! $coa_group) {
            $coa_group = new CoaGroup;
            $coa_group->coa_category_id = $coa_category_id;
            $coa_group->name = $name;
            $coa_group->created_by = 1;
            $coa_group->updated_by = 1;
            $coa_group->save();
        }

        return $coa_group;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('Point\Framework\Models\Master\CoaCategory', 'coa_category_id');
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

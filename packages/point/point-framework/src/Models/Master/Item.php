<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\MasterTrait;
use Point\Framework\Helpers\InventoryHelper;

class Item extends Model
{
    protected $table = 'item';

    use HistoryTrait, ByTrait, MasterTrait;

    /**
     * @param $q
     * @param $search
     * @return mixed
     */
    public function scopeSearch($q, $disabled, $search)
    {
        $array_of_search = explode(' ', $search);
        $q->where('item.disabled', '=', $disabled ? : 0);
        foreach ($array_of_search as $search) {
            $q->where(function ($query) use ($search, $disabled) {
                $query->where('item.name', 'like', '%'.$search.'%')
                    ->orWhere('item.code', 'like', '%'.$search.'%')
                    ->orWhere('item.notes', 'like', '%'.$search.'%');
            });
        }

        return $q;
    }

    public function averageCostOfSales($date)
    {
        return InventoryHelper::getAverageCostOfSales($date, $this->id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountAsset()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'account_asset_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unit()
    {
        return $this->hasMany('\Point\Framework\Models\Master\ItemUnit', 'item_id');
    }

    /**
     * @return mixed
     */
    public static function defaultUnit($item_id)
    {
        return ItemUnit::where('item_id', $item_id)->where('converter', '=', 1)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\ItemCategory', 'item_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\ItemType', 'item_type_id');
    }

    /**
     * @return string
     */
    public function getCodeNameAttribute()
    {
        return '['.$this->attributes['code'] . '] ' . $this->attributes['name'];
    }
}

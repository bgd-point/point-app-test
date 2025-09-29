<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\MasterTrait;

class Person extends Model
{
    protected $table = 'person';

    use HistoryTrait, ByTrait, MasterTrait;

    /**
     * @param $q
     * @param $person_type_id
     * @return mixed
     */
    public function scopeType($q, $person_type_id)
    {
        return $q->where('person_type_id', '=', $person_type_id);
    }

    /**
     * @param $q
     * @param $search
     * @return mixed
     */
    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%'.$search.'%')
            ->orWhere('code', 'like', '%'.$search.'%');
    }

    /**
     * @param $q
     * @param $person_type_id
     * @param $search
     * @return mixed
     */
    public function scopeSearchByType($q, $person_type_id, $disabled, $search)
    {
        \Log::info('helper: ' . $person_type_id);
        $group = PersonGroup::where('person_type_id', $person_type_id)->first();
        $response = $q->where('person_type_id', '=', $person_type_id)
            ->where(function ($query) use ($search, $group, $disabled) {
                $query->where('disabled', '=', $disabled ? : 0);
                if ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
                }
                
                $query->orWhere(function ($que) use ($group, $disabled, $search) {
                    if ($group) {
                        $que->where('disabled', '=', $disabled ? : 0)
                            ->where('person_group_id', $group->id);
                    }
                });
            });

        \Log::info($response->get());
        return $response;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\PersonGroup', 'person_group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\PersonType', 'person_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany('\Point\Framework\Models\Master\PersonContact', 'person_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function banks()
    {
        return $this->hasMany('\Point\Framework\Models\Master\PersonBank', 'person_id');
    }

    /**
     * @return string
     */
    public function getCodeNameAttribute()
    {
        return '['.$this->attributes['code'] . '] ' . $this->attributes['name'];
    }
}

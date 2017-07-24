<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

class PersonType extends Model
{
    protected $table = 'person_type';

    /**
     * Find person type by slug
     * @param $q
     * @param $slug
     * @return mixed
     */
    public function scopefindSlug($q, $slug)
    {
        return $q->whereIn('slug', $slug);
    }
}

<?php

namespace Point\Core\Models\Master;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';

    /**
     * @param $query
     * @param $history_table
     * @param $history_id
     * @return mixed
     */
    public function scopeShow($q, $history_table, $history_id)
    {
        return $q->where('history_table', '=', $history_table)
            ->where('history_id', '=', $history_id)
            ->orderBy('id', 'desc')
            ->take(100)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Point\Core\Models\User', 'user_id');
    }
}

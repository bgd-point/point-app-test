<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningInventory extends Model
{
    protected $table = 'opening_inventory';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('Point\Framework\Models\Master\Item', 'item_id');
    }

    public function formulir()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'formulir_id');
    }

    public function journals()
    {
        return $this->morphMany('Point\Framework\Models\Journal', 'journalable');
    }
}

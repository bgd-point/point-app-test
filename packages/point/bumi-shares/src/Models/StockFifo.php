<?php

namespace Point\BumiShares\Models;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\ByTrait;

class StockFifo extends Model
{
    protected $table = 'bumi_shares_stock_fifo';
    public $timestamps = false;

    public function scopeJoinFormulirSell($q)
    {
        $q->join('formulir', 'formulir.id', '=', $this->table.'.shares_out_id');
    }
    
    public function StockIn()
    {
        return $this->belongsTo('Point\BumiShares\Models\Stock', 'shares_in_id', 'formulir_id');
    }

    public function StockOut()
    {
        return $this->belongsTo('Point\BumiShares\Models\Stock', 'shares_out_id', 'formulir_id');
    }
}

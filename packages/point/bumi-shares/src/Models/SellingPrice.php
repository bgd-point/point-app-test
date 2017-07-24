<?php

namespace Point\BumiShares\Models;

use Illuminate\Database\Eloquent\Model;

class SellingPrice extends Model
{
    protected $table = 'bumi_shares_selling_price';

    public function shares()
    {
        return $this->belongsTo('Point\BumiShares\Models\Shares', 'shares_id');
    }
}

<?php

namespace Point\BumiShares\Models;

use Illuminate\Database\Eloquent\Model;
use Point\BumiShares\Models\Buy;
use Point\BumiShares\Models\Sell;

use Point\Core\Traits\ByTrait;

class Stock extends Model
{
    protected $table = 'bumi_shares_stock';
    public $timestamps = false;

    public function formulir()
    {
        return $this->belongsTo('Point\Framework\Models\Formulir', 'formulir_id');
    }

    public function scopeForm($q, $formulir_id)
    {
        $q->join('formulir', 'formulir.id', '=', 'bumi_shares_stock.formulir_id')->select(['*', \DB::raw('bumi_shares_stock.*')])->where('formulir_id', '=', $formulir_id);
    }

    public static function reference($formulir_id)
    {
        $reference = Buy::where('formulir_id', '=', $formulir_id)->first();
        
        if ($reference) {
            return $reference;
        } else {
            $reference = Sell::where('formulir_id', '=', $formulir_id)->first();
            if ($reference) {
                return $reference;
            }
        }
    }

    public function shares()
    {
        return $this->belongsTo('Point\BumiShares\Models\Shares', 'shares_id');
    }

    public function broker()
    {
        return $this->belongsTo('Point\BumiShares\Models\Broker', 'broker_id');
    }

    public function owner()
    {
        return $this->belongsTo('Point\BumiShares\Models\Owner', 'owner_id');
    }

    public function ownerGroup()
    {
        return $this->belongsTo('Point\BumiShares\Models\OwnerGroup', 'owner_group_id');
    }

    public static function availableStock($shares_id, $shares_owner_group_id, $broker_id, $owner_id)
    {
        $stock = Stock::where('shares_id', '=', $shares_id)
            ->where('owner_group_id', '=', $shares_owner_group_id)
            ->where('broker_id', '=', $broker_id)
            ->where('owner_id', '=', $owner_id)
            ->select(\DB::raw('sum(remaining_quantity) as remaining_quantity'))->first();

        if ($stock) {
            return $stock->remaining_quantity;
        }

        return 0;
    }
}

<?php 

namespace Point\BumiShares\Models;

use Point\Framework\Models\Formulir;
use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class Buy extends Model
{
    protected $table = 'bumi_shares_buy';
    public $timestamps = false;

    use ByTrait, FormulirTrait;

    /**
     * Inject function when saving
     *
     * @param array $options
     *
     * @return bool|null
     */
    public function save(array $options = [])
    {
        parent::save();

        $this->formulir->formulirable_type = get_class($this);
        $this->formulir->formulirable_id = $this->id;
        $this->formulir->save();

        return $this;
    }

    public function scopeJoinDependence($q)
    {
        $q->joinFormulir()->joinShares()->joinBroker()->joinOwner()->joinOwnerGroup();
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', $this->table.'.formulir_id');
    }

    public function scopeJoinShares($q)
    {
        $q->join('bumi_shares', 'bumi_shares.id', '=', $this->table.'.shares_id');
    }

    public function scopeJoinBroker($q)
    {
        $q->join('bumi_shares_broker', 'bumi_shares_broker.id', '=', $this->table.'.broker_id');
    }

    public function scopeJoinOwner($q)
    {
        $q->join('bumi_shares_owner', 'bumi_shares_owner.id', '=', $this->table.'.owner_id');
    }

    public function scopeJoinOwnerGroup($q)
    {
        $q->join('bumi_shares_owner_group', 'bumi_shares_owner_group.id', '=', $this->table.'.owner_group_id');
    }

    public function scopeForm($q, $formulir_id)
    {
        $q->join('formulir', 'formulir.id', '=', 'bumi_shares_buy.formulir_id')->select(['*', \DB::raw('bumi_shares_buy.*')])->where('formulir_id', '=', $formulir_id);
    }

    public function shares()
    {
        return $this->belongsTo('Point\BumiShares\Models\Shares', 'shares_id');
    }

    public function broker()
    {
        return $this->belongsTo('Point\BumiShares\Models\Broker', 'broker_id');
    }

    public function ownerGroup()
    {
        return $this->belongsTo('Point\BumiShares\Models\OwnerGroup', 'owner_group_id');
    }

    public function owner()
    {
        return $this->belongsTo('Point\BumiShares\Models\Owner', 'owner_id');
    }
}

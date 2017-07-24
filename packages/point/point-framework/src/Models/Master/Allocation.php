<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\MasterTrait;

class Allocation extends Model
{
    protected $table = 'allocation';

    use HistoryTrait, ByTrait, MasterTrait;

    public function scopeSearch($q, $disabled, $search)
    {
    	return $q->where('disabled', $disabled ? : 0)
        		->where(function($query) use ($disabled, $search) {
        			$query->where('disabled', $disabled ? : 0);
        			if ($search) {
        				$query->where('name', 'like', '%'.$search.'%');
        			}
        		});
    }
}

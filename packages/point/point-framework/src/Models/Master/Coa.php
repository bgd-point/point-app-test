<?php 

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;
use Point\Core\Traits\MasterTrait;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Person;
use Point\Framework\Traits\CoaFixedAsset;
use Point\Framework\Traits\CoaRelation;
use Point\Framework\Traits\CoaScopeQuery;
use Point\Framework\Traits\CoaSeederFunction;

class Coa extends Model
{
    protected $table = 'coa';

    use HistoryTrait, ByTrait, MasterTrait, CoaSeederFunction, CoaRelation, CoaFixedAsset, CoaScopeQuery;

    /**
     * Concat coa number and name
     *
     * @return string
     */
    public function getAccountAttribute()
    {
        return $this->attributes['coa_number'] . ' ' . $this->attributes['name'];
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public static function getByPosition($name)
    {
        return Coa::join('coa_category', 'coa.coa_category_id', '=', 'coa_category.id')
            ->join('coa_position', 'coa_category.coa_position_id', '=', 'coa_position.id')
            ->where('coa_position.name', '=', $name)
            ->selectOriginal()
            ->get();
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public static function getByCategory($name)
    {
        return Coa::join('coa_category', 'coa.coa_category_id', '=', 'coa_category.id')
            ->where('coa_category.name', '=', $name)
            ->selectOriginal()
            ->get();
    }

    public static function getNonSubledger()
    {
        return Coa::active()->where('has_subledger', 0)->get();
    }

    public static function getSubledger()
    {
        return Coa::active()->where('has_subledger', 1)->get();
    }

    public static function getSubledgerPerson()
    {
        return Coa::active()->where('has_subledger', 1)->where('subledger_type', get_class(New Person()))->get();
    }

    public function isUse()
    {
        $journal = Journal::where('coa_id', $this->id)->first();
        if ($journal) {
            return true;
        }

        return false;
    }
}

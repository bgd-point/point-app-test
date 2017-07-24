<?php

namespace Point\Framework\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class FixedAssetsContractReference extends Model
{
    protected $table = 'fixed_assets_contract_reference';
    public $timestamps = false;

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'supplier_id');
    }

    public function formulir()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'form_reference_id');
    }

    public function journal()
    {
        return $this->belongsTo('\Point\Framework\Models\Journal', 'journal_id');
    }
}

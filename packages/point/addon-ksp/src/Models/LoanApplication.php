<?php

namespace Point\Ksp\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Framework\Traits\FormulirTrait;

class LoanApplication extends Model
{
    protected $table = 'ksp_loan_application';
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

    public function customer()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Person', 'customer_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'payment_account_id');
    }
}

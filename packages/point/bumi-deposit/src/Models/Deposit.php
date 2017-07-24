<?php

namespace Point\BumiDeposit\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'bumi_deposit';
    public $timestamps = false;

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

    public function scopeJoinDependencies($q)
    {
        $q->joinDepositCategory()->joinDepositGroup()->joinBank();
    }

    public function scopeJoinDepositCategory($q)
    {
        $q->join('bumi_deposit_category', 'bumi_deposit_category.id', '=', 'bumi_deposit.deposit_category_id');
    }

    public function scopeJoinDepositGroup($q)
    {
        $q->join('bumi_deposit_group', 'bumi_deposit_group.id', '=', 'bumi_deposit.deposit_group_id');
    }

    public function scopeJoinBank($q)
    {
        $q->join('bumi_deposit_bank', 'bumi_deposit_bank.id', '=', 'bumi_deposit.deposit_bank_id');
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'bumi_deposit.formulir_id');
    }

    public function scopeSelectOriginal($q)
    {
        $q->select(['bumi_deposit.*']);
    }

    public function scopeOrderByDueDate($q)
    {
        $q->orderBy(\DB::raw('due_date'), 'asc')
            ->orderBy(\DB::raw('form_date', 'asc'));
    }

    public function scopeOrderByStandard($q)
    {
        $q->orderBy(\DB::raw('id'), 'desc')
            ->orderBy(\DB::raw('form_number', 'desc'));
    }

    public function scopeOrderByGroup($q)
    {
        $q->orderBy(\DB::raw('deposit_group_id'), 'desc');
    }

    public function scopeNotArchived($q, $form_number = 0)
    {
        $q->whereNotNull('formulir.form_number');
        if ($form_number) {
            $q->where('formulir.form_number', '=', $form_number);
        }
    }

    public function scopeArchived($q, $form_number = 0)
    {
        $q->whereNull('formulir.form_number');
        if ($form_number) {
            $q->where('formulir.archived', '=', $form_number);
        }
    }

    public function scopeActive($q)
    {
        $q->where('formulir.form_status', '!=', -1);
    }

    public function formulir()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'formulir_id');
    }

    public function reference()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\Deposit', 'reference_deposit_id');
    }

    public function category()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\DepositCategory', 'deposit_category_id');
    }

    public function group()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\DepositGroup', 'deposit_group_id');
    }

    public function owner()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\DepositOwner', 'deposit_owner_id');
    }

    public function bank()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\Bank', 'deposit_bank_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\BankAccount', 'deposit_bank_account_id');
    }

    public function bankProduct()
    {
        return $this->belongsTo('\Point\BumiDeposit\Models\BankProduct', 'deposit_bank_product_id');
    }

    public function withdrawApprovalTo()
    {
        return $this->belongsTo('\Point\Core\Models\User', 'withdraw_approval_to');
    }

    public static function showUrl($id)
    {
        $deposit = Deposit::find($id);

        if ($deposit->formulir->form_number) {
            return '/facility/bumi-deposit/deposit/'.$id;
        } else {
            return '/facility/bumi-deposit/deposit/'.$id.'/archived';
        }
    }
}

<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Exceptions\PointException;
use Point\Framework\Helpers\AccountPayableAndReceivableHelper;
use Point\Framework\Traits\FormulirTrait;
use Point\PointAccounting\Models\CutOffPayableDetail;
use Point\PointAccounting\Models\CutOffReceivableDetail;
use Point\PointSales\Models\Sales\Retur;

class Journal extends Model
{
    protected $table = 'journal';
    public $timestamps = false;

    use FormulirTrait;

    public function correctPositionJournal()
    {
        if ($this->debit < 0) {
            $this->credit += $this->debit * -1;
            $this->debit = 0;
        } elseif ($this->debit > 0) {
            $this->credit = 0;
        } elseif ($this->credit < 0) {
            $this->debit += $this->credit * -1;
            $this->credit = 0;
        } elseif ($this->credit > 0) {
            $this->debit = 0;
        }
    }

    private function insertSubledgerAccountPayable($options = [])
    {
        if ($this->credit > 0) {
            $account_payable_and_receivable = new AccountPayableAndReceivable;
            $account_payable_and_receivable->account_id = $this->coa_id;
            $account_payable_and_receivable->form_date = $this->form_date;
            $account_payable_and_receivable->formulir_reference_id = $this->form_journal_id;
            $account_payable_and_receivable->amount = $this->credit;
            $account_payable_and_receivable->type = 'payable';
            $account_payable_and_receivable->notes = $this->description;
            $account_payable_and_receivable->person_id = $this->subledger_id;
            if (is_array($options) && array_key_exists('reference_type', $options) && array_key_exists('reference_id', $options)) {
                $account_payable_and_receivable->reference_type = $options['reference_type'];
                $account_payable_and_receivable->reference_id = $options['reference_id'];
            }
            $account_payable_and_receivable->save();
        } elseif ($this->debit > 0) {
            $account_payable_and_receivable_id = '';
            $account_payable_and_receivable = AccountPayableAndReceivable::where('formulir_reference_id', $this->form_reference_id)
                    ->where('person_id', $this->subledger_id)
                    ->first();

            if (! $account_payable_and_receivable) {
                return;
            }

            if (array_key_exists('reference_type', $options) && array_key_exists('reference_id', $options)) {
                if ($options['reference_type'] && $options['reference_id']) {
                    $account_payable_and_receivable_reference = AccountPayableAndReceivable::where('reference_type', $options['reference_type'])
                        ->where('reference_id', $options['reference_id'])
                        ->first();
                    
                    if ($account_payable_and_receivable_reference) {
                        $account_payable_and_receivable_id = $account_payable_and_receivable_reference->id;
                    }
                }
            }

            if (! $account_payable_and_receivable_id) {
                $account_payable_and_receivable_id = $account_payable_and_receivable->id;
            }

            if (AccountPayableAndReceivableHelper::isDone($account_payable_and_receivable_id)) {
                if ($check->reference_type == get_class(new CutOffReceivableDetail()) || $check->reference_type == get_class(new CutOffPayableDetail())) {
                    return true;
                }
                throw new PointException('PAYMENT HAS DONE #'.$account_payable_and_receivable_id);
            }

            $account_payable_and_receivable_detail = new AccountPayableAndReceivableDetail;
            $account_payable_and_receivable_detail->account_payable_and_receivable_id = $account_payable_and_receivable_id;
            $account_payable_and_receivable_detail->formulir_reference_id = $this->form_journal_id;
            $account_payable_and_receivable_detail->amount = $this->debit;
            $account_payable_and_receivable_detail->form_date = $this->form_date;
            $account_payable_and_receivable_detail->notes = $this->description;
            $account_payable_and_receivable_detail->save();

            AccountPayableAndReceivableHelper::updateStatus($account_payable_and_receivable_id);

            return $account_payable_and_receivable_detail;
        }
    }

    private function insertSubledgerAccountReceivable($options = [])
    {
        if ($this->debit > 0) {
            $account_payable_and_receivable = new AccountPayableAndReceivable;
            $account_payable_and_receivable->account_id = $this->coa_id;
            $account_payable_and_receivable->form_date = $this->form_date;
            $account_payable_and_receivable->formulir_reference_id = $this->form_journal_id;
            $account_payable_and_receivable->amount = $this->debit;
            $account_payable_and_receivable->type = 'receivable';
            $account_payable_and_receivable->notes = $this->description;
            $account_payable_and_receivable->person_id = $this->subledger_id;
            if (is_array($options) && array_key_exists('reference_type', $options) && array_key_exists('reference_id', $options)) {
                $account_payable_and_receivable->reference_type = $options['reference_type'];
                $account_payable_and_receivable->reference_id = $options['reference_id'];
            }
            $account_payable_and_receivable->save();
        } elseif ($this->credit > 0) {
            $account_payable_and_receivable_id = '';
            $account_payable_and_receivable = AccountPayableAndReceivable::where('formulir_reference_id', $this->form_reference_id)
                    ->where('person_id', $this->subledger_id)
                    ->first();

            if (! $account_payable_and_receivable) {
                return;
            }

            if (array_key_exists('reference_type', $options) && array_key_exists('reference_id', $options)) {
                if ($options['reference_type'] && $options['reference_id']) {
                    $account_payable_and_receivable_reference = AccountPayableAndReceivable::where('reference_type', $options['reference_type'])
                        ->where('reference_id', $options['reference_id'])
                        ->first();
                    
                    if ($account_payable_and_receivable_reference) {
                        $account_payable_and_receivable_id = $account_payable_and_receivable_reference->id;
                    }
                }
            }

            if (! $account_payable_and_receivable_id) {
                $account_payable_and_receivable_id = $account_payable_and_receivable->id;
            }

            if (AccountPayableAndReceivableHelper::isDone($account_payable_and_receivable_id)) {
                $check = AccountPayableAndReceivable::find($account_payable_and_receivable_id);
                if ($check->reference_type == get_class(new CutOffReceivableDetail()) || $check->reference_type == get_class(new CutOffPayableDetail())) {
                    return true;
                }
                throw new PointException('PAYMENT HAS DONE #' . $check->formulirReference->form_number);
            }

            $account_payable_and_receivable_detail = new AccountPayableAndReceivableDetail;
            $account_payable_and_receivable_detail->account_payable_and_receivable_id = $account_payable_and_receivable_id;
            $account_payable_and_receivable_detail->formulir_reference_id = $this->form_journal_id;
            $account_payable_and_receivable_detail->amount = $this->credit;
            $account_payable_and_receivable_detail->form_date = $this->form_date;
            $account_payable_and_receivable_detail->notes = $this->description;
            $account_payable_and_receivable_detail->save();

            AccountPayableAndReceivableHelper::updateStatus($account_payable_and_receivable_id);

            return $account_payable_and_receivable_detail;
        }
    }

    private function insertSubledgerMutation($options)
    {
        if ($this->coa && $this->coa->has_subledger && $this->reference && $this->reference->formulirable_type != Retur::class) {
            if ($this->coa->category->name == 'Current Liability') {
                return $this->insertSubledgerAccountPayable($options);
            }

            if ($this->coa->category->name == 'Account Receivable') {
                return $this->insertSubledgerAccountReceivable($options);
            }
        }
    }

    private function isDebitCreditHasNoValue()
    {
        if ($this->debit == 0 && $this->credit == 0) {
            return true;
        }

        return false;
    }

    public function save(array $options = [])
    {
        // if ($this->isDebitCreditHasNoValue()) {
        //     return null;
        // }

        $this->correctPositionJournal();
        $this->insertSubledgerMutation($options);
        return parent::save();
    }

    public function scopeJoinCoa($q)
    {
        $q->join('coa', 'coa.id', '=', 'journal.coa_id');
    }

    public function scopeJoinReference($q)
    {
        $q->join('formulir', 'formulir.id', '=', 'journal.form_journal_id');
    }
    
    public function scopeCoaHasSubleger($q)
    {
        $q->where('coa.has_subledger', '=', 1);
    }

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }

    public function formulir()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'form_journal_id');
    }

    public function reference()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'form_reference_id');
    }
}

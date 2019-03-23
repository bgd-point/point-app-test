<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Traits\ByTrait;
use Point\PointFinance\Models\CashAdvance;

class Formulir extends Model
{
    protected $table = 'formulir';

    use ByTrait;

    public function save(array $options = [])
    {
        $lockedDate = strtotime('2019-03-01');

        if (request()->get('database_name') == 'p_kbretail'
            && strtotime($this->form_date) < $lockedDate
            && $this->formulirable_type != CashAdvance::class) {
            throw new PointException('You cannot change data before 01 November 2018, or you can contact your administrator');
        }

        return parent::save($options);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvalTo()
    {
        return $this->belongsTo('Point\Core\Models\User', 'approval_to');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function canceledBy()
    {
        return $this->belongsTo('Point\Core\Models\User', 'canceled_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('Point\Core\Models\User', 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function formulirable()
    {
        return $this->morphTo();
    }

    public function emailHistory()
    {
        return $this->hasMany('Point\Framework\Models\EmailHistory');
    }

    public function archives()
    {
        return $this->hasMany('Point\Framework\Models\Formulir', 'archived', 'form_number');
    }

    public function scopeNotArchived($q, $form_number = 0)
    {
        $q->whereNotNull('form_number');
        if ($form_number) {
            $q->where('form_number', '=', $form_number);
        }
    }

    public function scopeArchived($q, $form_number = 0)
    {
        $q->whereNull('form_number');
        if ($form_number) {
            $q->where('archived', '=', $form_number);
        }
    }

    public function scopeNotCanceled($q)
    {
        $q->where('form_status', '!=', -1);
    }

    public function scopeCanceled($q)
    {
        $q->where('form_status', '!=', -1);
    }

    public function scopeOpen($q)
    {
        $q->where('form_status', '=', 0);
    }

    public function scopeClose($q)
    {
        $q->where('form_status', '=', 1);
    }

    public function scopeApprovalPending($q)
    {
        $q->where('approval_status', '=', 0);
    }

    public function scopeApprovalApproved($q)
    {
        $q->where('approval_status', '=', 1);
    }

    public function scopeApprovalRejected($q)
    {
        $q->where('approval_status', '=', -1);
    }
}

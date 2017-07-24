<?php

namespace Point\Framework\Traits;

trait FormulirTrait
{

    /**
     * Query for email send request approval
     *
     * @param $q
     *
     * @return mixed
     */
    public function scopeSelectRequestApproval($q)
    {
        return $q->joinFormulir()->notArchived()->notCanceled()->approvalPending()->selectOriginal();
    }

    /**
     * Query for select list of approver from selected data
     *
     * @param       $q
     * @param array $formulir_id
     *
     * @return mixed
     */
    public function scopeSelectApproverList($q, $formulir_id = [])
    {
        return $q->joinFormulir()
            ->whereIn('formulir_id', $formulir_id)
            ->groupBy('formulir.approval_to')
            ->select('formulir.approval_to as approval_to')
            ->get();
    }

    /**
     * Query for select data from selected approver
     *
     * @param       $q
     * @param array $formulir_id
     * @param       $approver_id
     *
     * @return mixed
     */
    public function scopeSelectApproverRequest($q, $formulir_id = [], $approver_id)
    {
        return $q->joinFormulir()
            ->whereIn('formulir_id', $formulir_id)
            ->where('formulir.approval_to', '=', $approver_id)
            ->selectOriginal()
            ->get();
    }

    public function scopeSelectArchived($q, $form_number)
    {
        return $q->joinFormulir()->archived($form_number)->selectOriginal()->get();
    }

    public function scopeSelectNotArchived($q, $form_number)
    {
        return $q->joinFormulir()->notArchived($form_number)->selectOriginal()->first();
    }

    public function scopeJoinFormulir($q)
    {
        $q->join('formulir', 'formulir.id', '=', $this->table.'.formulir_id');
    }

    public function scopeOrderByStandard($q)
    {
        $q->orderBy(\DB::raw('CAST(form_date as date)'), 'desc')
            ->orderBy(\DB::raw('SUBSTRING_INDEX(form_number, "/", -2)'));
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

    public function scopeSelectOriginal($q)
    {
        $q->select([$this->table.'.*']);
    }

    public function scopeNotCanceled($q)
    {
        $q->where('formulir.form_status', '!=', -1);
    }

    public function scopeCanceled($q)
    {
        $q->where('formulir.form_status', '=', -1);
    }

    public function scopeOpen($q)
    {
        $q->where('formulir.form_status', '=', 0);
    }

    public function scopeClose($q)
    {
        $q->where('formulir.form_status', '=', 1);
    }

    public function scopeApprovalPending($q)
    {
        $q->where('formulir.approval_status', '=', 0);
    }

    public function scopeApprovalApproved($q)
    {
        $q->where('formulir.approval_status', '=', 1);
    }

    public function scopeApprovalRejected($q)
    {
        $q->where('formulir.approval_status', '=', -1);
    }

    public function formulir()
    {
        return $this->belongsTo('\Point\Framework\Models\Formulir', 'formulir_id');
    }

    public function lockedBy()
    {
        return $this->hasMany('\Point\Framework\Models\FormulirLock', 'locked_id', 'formulir_id');
    }
}

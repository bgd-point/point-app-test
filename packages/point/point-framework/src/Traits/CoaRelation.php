<?php

namespace Point\Framework\Traits;

trait CoaRelation
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groupCategory()
    {
        return $this->belongsTo('Point\Framework\Models\Master\CoaGroupCategory', 'coa_group_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('Point\Framework\Models\Master\CoaCategory', 'coa_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('Point\Framework\Models\Master\CoaGroup', 'coa_group_id');
    }

    public function totalDebit($date)
    {
        $date_form = date("Y-m-d 00:00:00", strtotime($date));
        $date_to = date("Y-m-t 23:59:59", strtotime($date));
        return $this->hasMany('\Point\Framework\Models\Journal', 'coa_id')
            ->whereBetween('form_date', [$date_form, $date_to])->sum('debit');
    }

    public function totalCredit($date)
    {
        $date_form = date("Y-m-d 00:00:00", strtotime($date));
        $date_to = date("Y-m-t 23:59:59", strtotime($date));
        return $this->hasMany('\Point\Framework\Models\Journal', 'coa_id')
            ->whereBetween('form_date', [$date_form, $date_to])->sum('credit');
    }

    public function value($date)
    {
        $date_form = date("Y-m-d 00:00:00", strtotime($date));
        $date_to = date("Y-m-t 23:59:59", strtotime($date));

        $debit = $this->hasMany('\Point\Framework\Models\Journal', 'coa_id')
            ->whereBetween('form_date', [$date_form, $date_to])
            ->sum('debit');

        $credit = $this->hasMany('\Point\Framework\Models\Journal', 'coa_id')
            ->whereBetween('form_date', [$date_form, $date_to])
            ->sum('credit');

        if ($this->category->position == 'debit') {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }
}

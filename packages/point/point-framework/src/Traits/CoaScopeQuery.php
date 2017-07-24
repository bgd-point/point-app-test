<?php

namespace Point\Framework\Traits;

trait CoaScopeQuery
{
    public function scopeJoinCategory($q)
    {
        return $q->join('coa_category', 'coa_category.id', '=', 'coa.coa_category_id');
    }

    public function scopeSelectOriginal($q)
    {
        return $q->select('coa.*');
    }

    public function scopeSearch($q, $search)
    {
        return $q->where('name', 'like', '%' . $search . '%')->orWhere('coa_number', 'like', '%' . $search . '%');
    }

    public function scopeHasNotSubledger($q)
    {
        return $q->where('has_subledger', 0);
    }

    public function scopeHasSubledger($q)
    {
        return $q->where('has_subledger', 1);
    }
}

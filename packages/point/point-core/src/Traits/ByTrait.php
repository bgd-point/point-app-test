<?php

namespace Point\Core\Traits;

trait ByTrait
{
    public function createdBy()
    {
        return $this->belongsTo('Point\Core\Models\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('Point\Core\Models\User', 'updated_by');
    }
}

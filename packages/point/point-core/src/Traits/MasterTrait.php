<?php

namespace Point\Core\Traits;

trait MasterTrait
{
    public function scopeActive($query)
    {
        return $query->where('disabled', '=', false);
    }

    public function scopeDisabled($query)
    {
        return $query->where('disabled', '=', true);
    }
}

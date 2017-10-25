<?php

namespace Point\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\Core\Traits\HistoryTrait;

class Setting extends Model
{
    use HistoryTrait, ByTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';

    public function scopeRightClickAllowed($q)
    {
        return $q->where('name', '=', 'right-click-allowed')->first()->value;
    }

    public function scopeMouseSelectAllowed($q)
    {
        return $q->where('name', '=', 'mouse-select-allowed')->first()->value;
    }

    public function scopeUserChangePasswordAllowed($q)
    {
        return $q->where('name', '=', 'user-change-password-allowed')->first()->value;
    }

    public static function getFontSize()
    {
        return Setting::where('name', 'pos-print-font-size')->first() ? Setting::where('name', 'pos-print-font-size')->first()->value : 14;
    }
}

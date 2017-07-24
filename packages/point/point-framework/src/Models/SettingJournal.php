<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class SettingJournal extends Model
{
    protected $table = 'setting_journal';
    public $timestamps = false;

    public static function exist($group, $name)
    {
        if (SettingJournal::where('group', '=', $group)->where('name', '=', $name)->first()) {
            return true;
        }

        return false;
    }

    public static function insert($group, $name, $coa_id)
    {
        if (! self::exist($group, $name)) {
            $setting_journal = new SettingJournal;
            $setting_journal->group = $group;
            $setting_journal->name = $name;
            $setting_journal->description = '';
            $setting_journal->coa_id = $coa_id;
            $setting_journal->save();
        }
    }

    public static function isSetup($group)
    {
        $list_journal = SettingJournal::where('group', '=', $group)->get();

        foreach ($list_journal as $journal) {
            if (! $journal->coa) {
                return false;
            }
        }

        return true;
    }

    public function coa()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Coa', 'coa_id');
    }
}

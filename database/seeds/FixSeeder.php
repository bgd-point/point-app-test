<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaGroupCategory;
use Point\Framework\Models\SettingJournal;

class FixSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $setting_journal = SettingJournal::where('name', '=', 'advance to employees');
        if ($setting_journal) {
            $setting_journal->delete();
        } else {
            \Log::info('fail');
        }

        $coa = Coa::where('name', 'Advance to Employees')->first();
        if ($coa) {
            $coa->delete();
        } else {
            \Log::info('fail2');
        }

        \DB::commit();
    }
}

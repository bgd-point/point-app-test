<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaGroupCategory;
use Point\Framework\Models\SettingJournal;
use Point\PointFinance\Models\Cash\Cash;

class FixSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $setting_journal = SettingJournal::where('name', '=', 'advance to employees');
        if ($setting_journal) {
            $setting_journal->delete();
        }

        $coa = Coa::where('name', 'Advance to Employees')->first();
        if ($coa) {
            $coa->delete();
        }

        \DB::commit();
    }
}

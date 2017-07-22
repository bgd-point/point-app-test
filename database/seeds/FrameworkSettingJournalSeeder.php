<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;

class FrameworkSettingJournalSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // setup account journal for opening balance
        $coa = Coa::where('name', '=', 'Retained Earning')->first();
        SettingJournal::insert('opening balance inventory', 'retained earning', $coa ? $coa->id : null);
    }
}

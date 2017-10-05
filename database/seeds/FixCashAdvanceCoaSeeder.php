<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaCategory;

class FixCashAdvanceCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Assets account
        $coa_category = CoaCategory::where('name', '=',  'Petty Cash')->first();
        Coa::insert($coa_category->id, 'Advance to Employees');
    }
}

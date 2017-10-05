<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\Master\CoaGroup;

class FixChequeCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coa_category = CoaCategory::where('name', '=',  'Account Receivable')->first();
        Coa::insert($coa_category->id, 'Cheque Receivable', true, 'Point\Framework\Models\Master\Person');

        $coa_category = CoaCategory::where('name', '=',  'Account Payable')->first();
        Coa::insert($coa_category->id, 'Cheque Payable', true, 'Point\Framework\Models\Master\Person');
    }
}

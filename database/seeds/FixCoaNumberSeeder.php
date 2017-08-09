<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;

class FixCoaNumberSeeder extends Seeder
{
    public function run()
    {
    	\DB::beginTransaction();
        \Log::info('Fix coa number seeder started');
        
        $list_coa = Coa::all();
        foreach ($list_coa as $coa) {
        	$coa->coa_number = $coa->coa_number ? trim($coa->coa_number) : null;
        	$coa->name = trim($coa->name);
        	$coa->save();
        }

        \Log::info('Fix coa number seeder finished');
        \DB::commit();
    }
}
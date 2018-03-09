<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;

class FixCloseCashAdvanceSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

//        $cash_advances = \Point\PointFinance\Models\CashAdvance::all();
//        foreach ($cash_advances as $cash_advance) {
//            if ($cash_advance->formulir->form_status) {
//                $cash_advance->remaining_amount = 0;
//                $cash_advance->save();
//            }
//        }

        \DB::commit();
    }
}

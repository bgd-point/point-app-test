<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;

class FixCloseCashAdvanceSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $cash_cash_advances = \Point\PointFinance\Models\Cash\CashCashAdvance::all();

        foreach ($cash_cash_advances as $cash_cash_advance) {
            if ($cash_cash_advance->used->form_status == -1) {
                $cash_cash_advance->cash_advance_amount = 0;
                $cash_cash_advance->save();
            }
        }

        $cash_advances = \Point\PointFinance\Models\CashAdvance::all();
        foreach ($cash_advances as $cash_advance) {
            \Log::info('' . $cash_advance->formulir->form_number);
            if ($cash_advance->formulir->form_status == -1) {
                $cash_advance->remaining_amount = 0;
                $cash_advance->save();
            } else {
                $amount = \Point\PointFinance\Models\Cash\CashCashAdvance::where('cash_advance_id', $cash_advance->id)->sum('cash_advance_amount');
                $cash_advance->remaining_amount = $cash_advance->amount - $amount;
                $cash_advance->save();
                if ($amount == $cash_advance->amount) {
                    $cash_advance->formulir->form_status = 1;
                    $cash_advance->formulir->save();
                } else {
                    $cash_advance->formulir->form_status = 0;
                    $cash_advance->formulir->save();
                }
            }
        }

        \DB::commit();
    }
}

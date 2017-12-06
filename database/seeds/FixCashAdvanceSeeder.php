<?php

use Illuminate\Database\Seeder;

class FixCashAdvanceSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $cash_cash_advances = \Point\PointFinance\Models\Cash\CashCashAdvance::all();
        \Log::info($cash_cash_advances->count());

        foreach($cash_cash_advances as $cash_cash_advance) {
            if ($cash_cash_advance->cashAdvance->payment_type == 'bank') {
                $cash_cash_advance->point_finance_cash_id = \Point\PointFinance\Models\Bank\Bank::find($cash_cash_advance->point_finance_cash_id)->formulir->id;
            } else {
                $cash_cash_advance->point_finance_cash_id = \Point\PointFinance\Models\Cash\Cash::find($cash_cash_advance->point_finance_cash_id)->formulir->id;
            }

            \Log::info($cash_cash_advance->id);
            if ($cash_cash_advance->used->form_status == -1) {
                \Log::info($cash_cash_advance->used->form_number);
                $cash_cash_advance->cashAdvance->remaining_amount += $cash_cash_advance->cash_advance_amount;
                $cash_cash_advance->cashAdvance->formulir->form_status = 0;
                $cash_cash_advance->cashAdvance->formulir->save();
                $cash_cash_advance->cashAdvance->save();
            }

            $cash_cash_advance->save();
        }

        \DB::commit();
    }
}

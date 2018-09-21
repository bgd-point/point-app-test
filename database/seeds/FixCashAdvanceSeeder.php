<?php

use Illuminate\Database\Seeder;

class FixCashAdvanceSeeder extends Seeder
{
    public function run()
    {
        $cash_advances = \Point\PointFinance\Models\CashAdvance::join('formulir', 'formulir.id', '=', 'point_finance_cash_advance.formulir_id')
            ->where('formulir.form_status', '!=', 0)
            ->select('point_finance_cash_advance.*')
            ->get();

        foreach($cash_advances as $cash_advance) {
            $cash_advance->remaining_amount = 0;
            $cash_advance->save();
        }

        //
        $cash_advances = \Point\PointFinance\Models\CashAdvance::join('formulir', 'formulir.id', '=', 'point_finance_cash_advance.formulir_id')
            ->where('point_finance_cash_advance.is_payed', 1)
            ->where('point_finance_cash_advance.remaining_amount', 0)
            ->select('point_finance_cash_advance.*')
            ->get();

        foreach($cash_advances as $cash_advance) {
            $cash_advance->formulir->form_status = 1;
            $cash_advance->formulir->save();
        }
    }
}

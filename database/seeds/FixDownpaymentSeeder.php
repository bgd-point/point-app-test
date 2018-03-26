<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Journal;
use Point\PointPurchasing\Models\Inventory\Downpayment as DP1;
use Point\PointPurchasing\Models\Service\Downpayment as DP2;
use Point\PointSales\Models\Sales\Downpayment as DP3;
use Point\PointSales\Models\Service\Downpayment as DP4;
use Point\PointExpedition\Models\Downpayment as DP5;

class FixDownpaymentSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $journals = Journal::join('formulir', 'formulir.id', '=', 'journal.form_reference_id')
            ->where('formulir.formulirable_type', 'like', '%Downpayment%')
            ->groupBy('journal.form_reference_id')
            ->get();

        foreach ($journals as $journal) {
            $debit = Journal::join('formulir', 'formulir.id', '=', 'journal.form_reference_id')
                ->where('journal.form_reference_id', $journal->form_reference_id)
                ->sum('debit');

            $credit = Journal::join('formulir', 'formulir.id', '=', 'journal.form_reference_id')
                ->where('journal.form_reference_id', $journal->form_reference_id)
                ->sum('credit');

            if ($journal->coa->category->position->debit) {
                $total = $debit - $credit;
            } else {
                $total = $credit - $debit;
            }

            $model = $journal->reference->formulirable_type;
            $dp = $model::find($journal->reference->formulirable_id);
            $dp->remaining_amount = $total;
            $dp->save();
        }

        $downpayments = DP1::joinFormulir()->where('formulir.form_status', -1)->selectOriginal()->get();
        foreach ($downpayments as $downpayment) {
            $downpayment->remaining_amount = 0;
            $downpayment->save();
        }

        $downpayments = DP2::joinFormulir()->where('formulir.form_status', -1)->selectOriginal()->get();
        foreach ($downpayments as $downpayment) {
            $downpayment->remaining_amount = 0;
            $downpayment->save();
        }

        $downpayments = DP3::joinFormulir()->where('formulir.form_status', -1)->selectOriginal()->get();
        foreach ($downpayments as $downpayment) {
            $downpayment->remaining_amount = 0;
            $downpayment->save();
        }

        $downpayments = DP4::joinFormulir()->where('formulir.form_status', -1)->selectOriginal()->get();
        foreach ($downpayments as $downpayment) {
            $downpayment->remaining_amount = 0;
            $downpayment->save();
        }

        $downpayments = DP5::joinFormulir()->where('formulir.form_status', -1)->selectOriginal()->get();
        foreach ($downpayments as $downpayment) {
            $downpayment->remaining_amount = 0;
            $downpayment->save();
        }

        \DB::commit();
    }
}

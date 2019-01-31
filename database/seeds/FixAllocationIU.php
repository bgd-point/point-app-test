<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;

class FixAllocationIU extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $salesReturns = \Point\PointSales\Models\Sales\Retur::all();

        foreach ($salesReturns as $return)
        {
            $ac = \Point\Framework\Models\AccountPayableAndReceivable::join('formulir',
                'formulir.id', '=', 'account_payable_and_receivable.formulir_reference_id')
                ->where('formulir.formulirable_type', \Point\PointSales\Models\Sales\Invoice::class)
                ->where('formulir.formulirable_id', $return->point_sales_invoice_id)
                ->select('account_payable_and_receivable.*')
                ->first();

            $accountP = new \Point\Framework\Models\AccountPayableAndReceivableDetail();
            $accountP->form_date = $return->formulir->form_date;
            $accountP->account_payable_and_receivable_id = $ac->id;
            $accountP->formulir_reference_id = $return->formulir_id;
            $accountP->amount = $return->total;
            $accountP->notes = 'Retur';
            $accountP->save();
        }

        \DB::commit();
    }
}

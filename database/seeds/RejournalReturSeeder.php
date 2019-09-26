<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;

class RejournalReturSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listRetur = \Point\PointSales\Models\Sales\Retur::joinFormulir()
            ->notArchived()
            ->approvalApproved()
            ->select('formulir.id')
            ->get()
            ->toArray();

        Journal::whereIn('form_journal_id', $listRetur)->delete();

        $listRetur = \Point\PointSales\Models\Sales\Retur::joinFormulir()
            ->where('formulir.form_status', '>=', 0)
            ->notArchived()
            ->approvalApproved()
            ->select('point_sales_retur.*')
            ->get();

        foreach ($listRetur as $retur) {
            // PENJUALAN (DEBIT)
            $sales_of_goods = JournalHelper::getAccount('point sales indirect', 'sale of goods');
            $journal = new Journal;
            $journal->form_date = $retur->formulir->form_date;
            $journal->coa_id = $sales_of_goods;
            $journal->description = 'retur invoice ' . $retur->invoice->formulir->form_number;
            $journal->debit = $retur->total;
            $journal->form_journal_id = $retur->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            // ACCOUNT RECEIVEABLE (CREDIT)
            $account_receivable = JournalHelper::getAccount('point sales indirect', 'account receivable');
            $journal = new Journal;
            $journal->form_date = $retur->formulir->form_date;
            $journal->coa_id = $account_receivable;
            $journal->description = 'retur invoice ' . $retur->formulir->form_number;
            $journal->credit = $retur->total;
            $journal->form_journal_id = $retur->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id = $retur->person_id;
            $journal->subledger_type = get_class($retur->person);
            $journal->save();

            // INVENTORY (DEBIT)
            foreach ($retur->items as $returItem) {

                $hpp = \Point\PointPurchasing\Models\Inventory\InvoiceItem::where('item_id', $returItem->item->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $price = 0;

                if (!$hpp) {
                    $outputItem = \Point\PointManufacture\Models\OutputProduct::where('product_id', '=', $returItem->item_id)
                        ->orderBy('id', 'desc')
                        ->first();

                    $input = \Point\PointManufacture\Models\InputProcess::find($outputItem->output->input_id);

                    $price = 0;

                    foreach ($input->material as $material) {
                        $item = \Point\PointPurchasing\Models\Inventory\InvoiceItem::where('item_id', $material->material_id)
                            ->orderBy('id', 'desc')
                            ->first();

                        if ($item) {
                            $price += $item->price;
                        } else {
//                            echo 'product ' . $returItem->item_id . ' | ';
//                            echo $material->material_id;
                            $price = 0;
                        }
                    }

//                    $hpp = \Point\Framework\Models\Inventory::where('item_id', $returItem->item_id)
//                        ->where('quantity', '>', 0)
//                        ->first();
                }

                if (!$hpp) {
                    info($returItem->item_id);
                } else {
                    $price = $hpp->price;
                }

                echo $returItem->item->code . ' ' . $returItem->item->name . ' | HPP ' . $price;
                echo PHP_EOL;

                $journal = new Journal;
                $journal->form_date = $retur->formulir->form_date;
                $journal->coa_id = $returItem->item->account_asset_id;
                $journal->description = 'retur item "' . $returItem->item->codeName.'"';
                $journal->credit = $price * $returItem->quantity;
                $journal->form_journal_id = $retur->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $returItem->item_id;
                $journal->subledger_type = get_class($returItem->item);
                $journal->save();

                // HPP (CREDIT)
                $cost_of_sales_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
                $journal = new Journal;
                $journal->form_date = $retur->formulir->form_date;
                $journal->coa_id = $cost_of_sales_account;
                $journal->description = 'retur item "' . $retur->formulir->form_number.'"';
                $journal->debit = $price * $returItem->quantity;
                $journal->form_journal_id = $retur->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }
    }
}

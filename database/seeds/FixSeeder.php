<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\MemoJournal;

class FixSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $dps = Formulir::where('formulirable_type', 'like', '%DOWNPAYMENT%')->where('form_status', '!=', 1)->get();

        foreach ($dps as $dp) {
            $locked = FormulirLock::where('locked_id', $dp->id)->where('locked', true)->first();
            if ($locked) {
                \Log::info($dp->form_number);
                $dp->form_status = 1;
                $dp->save();
            }
        }

//        $memoJournals = MemoJournal::all();

//        foreach($memoJournals as $memo_journal) {
//            Journal::where('form_journal_id', $memo_journal->formulir_id)->delete();
//
//            foreach ($memo_journal->detail as $memo_journal_item) {
//                $journal = new Journal;
//                $journal->form_date = $memo_journal->formulir->form_date;
//                $journal->coa_id = $memo_journal_item->coa_id;
//                $journal->description = $memo_journal_item->description;
//                $journal->debit = $memo_journal_item->debit;
//                $journal->credit = $memo_journal_item->credit;
//                $journal->form_journal_id = $memo_journal->formulir_id;
//                $journal->form_reference_id = $memo_journal_item->form_reference_id ?: null;
//                $journal->subledger_id = $memo_journal_item->subledger_id ?: null;
//                $journal->subledger_type = $memo_journal_item->subledger_type ?: null;
//
//                $journal->save();
//            }
//        }


//        $items = Item::all();
//        $index = 1;
//        foreach ($items as $item) {
//            $item->code = 'CODE-' . $index;
//            $item->name = 'ITEM NAME ' . $index;
//            $item->save();
//            $index++;
//        }
//
//        $index = 1;
//        $persons = Person::all();
//        foreach ($persons as $person) {
//            $person->code = 'CODE-' . $index;
//            $person->name = 'CUSTOMER ' . $index;
//            $person->save();
//            $index++;
//        }

//        \Point\Framework\Models\Master\AllocationReport::where('id','>',0)->delete();
//
//        $sales_invoices = \Point\PointSales\Models\Sales\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
//        foreach ($sales_invoices as $sales_invoice) {
//            foreach ($sales_invoice->items as $item) {
//                $total = $item->price * $item->quantity;
//                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
//                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount, $item->item_notes);
//            }
//        }
//
//        $sales_invoices = \Point\PointSales\Models\Service\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
//        foreach ($sales_invoices as $sales_invoice) {
//            foreach ($sales_invoice->items as $item) {
//                $total = $item->price * $item->quantity;
//                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
//                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount, $item->item_notes);
//            }
//
//            foreach ($sales_invoice->services as $item) {
//                $total = $item->price * $item->quantity;
//                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
//                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount, $item->service_notes);
//            }
//        }
//
//        $sales_invoices = Point\PointPurchasing\Models\Inventory\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
//        foreach ($sales_invoices as $sales_invoice) {
//            foreach ($sales_invoice->items as $item) {
//                $total = $item->price * $item->quantity;
//                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
//                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount * -1, $item->item_notes);
//            }
//        }
//
//        $sales_invoices = Point\PointPurchasing\Models\Service\Invoice::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
//        foreach ($sales_invoices as $sales_invoice) {
//            foreach ($sales_invoice->items as $item) {
//                $total = $item->price * $item->quantity;
//                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
//                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount * -1, $item->item_notes);
//            }
//
//            foreach ($sales_invoice->services as $item) {
//                $total = $item->price * $item->quantity;
//                $amount = $total - ($item->price * $item->quantity * $item->discount / 100);
//                AllocationHelper::save($sales_invoice->formulir->id, $item->allocation_id, $amount * -1, $item->service_notes);
//            }
//        }
//
////        $payment_orders = \Point\PointFinance\Models\PaymentOrder\PaymentOrder::joinFormulir()->notArchived()->notCanceled()->selectOriginal()->get();
////        foreach ($payment_orders as $order) {
////            foreach ($order->detail() as $details) {
////                AllocationHelper::save($order->formulir->id, $details->allocation_id, $detail->amount * -1, $details->notes_detail);
////            }
////        }
//
//        $cash_details = \Point\PointFinance\Models\Cash\CashDetail::joinCash()
//            ->notArchived()->notCanceled()->where('form_reference_id', NULL)->selectOriginal()->get();
//
//        foreach ($cash_details as $detail) {
//            AllocationHelper::save($detail->cash->formulir->id, $detail->allocation_id, $detail->amount, $detail->notes_detail);
//        }
//
//        $bank_details = \Point\PointFinance\Models\Bank\BankDetail::joinBank()
//            ->notArchived()->notCanceled()->where('form_reference_id', NULL)->selectOriginal()->get();
//
//        foreach ($bank_details as $detail) {
//            AllocationHelper::save($detail->bank->formulir->id, $detail->allocation_id, $detail->amount, $detail->notes_detail);
//        }

        \DB::commit();
    }
}

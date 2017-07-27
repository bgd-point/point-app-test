<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffPayableDetail;

class FixSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

//        \DB::statement('alter table `point_sales_payment_collection_detail` add `reference_id` int null, add `reference_type` varchar(255) null');
//        \DB::statement('alter table `point_sales_payment_collection_detail` add index `point_sales_payment_collection_detail_reference_id_index`(`reference_id`)');
//        \DB::statement('alter table `point_sales_payment_collection_detail` add index `point_sales_payment_collection_detail_reference_type_index`(`reference_type`)');
//
//        \DB::statement('alter table `point_expedition_payment_order_detail` add `reference_id` int null, add `reference_type` varchar(255) null');
//        \DB::statement('alter table `point_expedition_payment_order_detail` add index `point_expedition_payment_order_detail_reference_id_index`(`reference_id`)');
//        \DB::statement('alter table `point_expedition_payment_order_detail` add index `point_expedition_payment_order_detail_reference_type_index`(`reference_type`)');
//
//        \DB::statement('alter table `point_purchasing_payment_order_detail` add `reference_id` int null, add `reference_type` varchar(255) null');
//        \DB::statement('alter table `point_purchasing_payment_order_detail` add index `point_purchasing_payment_order_detail_reference_id_index`(`reference_id`)');
//        \DB::statement('alter table `point_purchasing_payment_order_detail` add index `point_purchasing_payment_order_detail_reference_type_index`(`reference_type`)');

        self::fixExpedition();

        \DB::commit();
    }

    public function fixExpedition()
    {
        $co_payable = new CutOffPayableDetail;
        $co_payable->cut_off_payable_id = 6;
        $co_payable->coa_id = 28;
        $co_payable->subledger_type = get_class(new Person());
        $co_payable->subledger_id = 92; // samaraya
        $co_payable->amount = 37500000;
        $co_payable->notes = 'cutoff utang angkutan';
        $co_payable->save();

        $co_payable = new CutOffPayableDetail;
        $co_payable->cut_off_payable_id = 6;
        $co_payable->coa_id = 28;
        $co_payable->subledger_type = get_class(new Person());
        $co_payable->subledger_id = 91; // laut mandiri
        $co_payable->amount = 11919530;
        $co_payable->notes = 'cutoff utang angkutan';
        $co_payable->save();

        $co_payable = new CutOffPayableDetail;
        $co_payable->cut_off_payable_id = 6;
        $co_payable->coa_id = 28;
        $co_payable->subledger_type = get_class(new Person());
        $co_payable->subledger_id = 96; // trucking
        $co_payable->amount = 2618500;
        $co_payable->notes = 'cutoff utang angkutan';
        $co_payable->save();

        $co_payable = new CutOffPayableDetail;
        $co_payable->cut_off_payable_id = 6;
        $co_payable->coa_id = 28;
        $co_payable->subledger_type = get_class(new Person());
        $co_payable->subledger_id = 97; // lain-lain
        $co_payable->amount = 166250;
        $co_payable->notes = 'cutoff utang angkutan';
        $co_payable->save();

        $cut_off_account = CutOffAccount::find(11);

        foreach(CutOffPayableDetail::where('notes', 'cutoff utang angkutan')->get() as $detail) {
            \Log::info($detail->coa_id. ' payable');
            $journal = new Journal();
            $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
            $journal->coa_id = $detail->coa_id;
            $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
            $journal->debit = 0;
            $journal->credit = $detail->amount;
            $journal->form_journal_id = 683;
            $journal->form_reference_id;
            $journal->subledger_id = $detail->subledger_id;
            $journal->subledger_type = $detail->subledger_type;
            $journal->save(['reference_type' => get_class($detail), 'reference_id' => $detail->id]);
        }
    }
}

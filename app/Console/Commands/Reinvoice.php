<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;

class Reinvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reinvoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change invoice date';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('change inventory date');
        \DB::beginTransaction();

        // get invoices date and its receive date
        $invoices = \DB::table('point_purchasing_invoice AS ppi')
            ->join('formulir_lock AS fl', 'fl.locking_id', '=', 'ppi.formulir_id')
            ->join('point_purchasing_goods_received AS ppgr', 'ppgr.formulir_id', '=', 'fl.locked_id')
            ->join('formulir AS f1', 'f1.id', '=', 'fl.locking_id')
            ->join('formulir AS f2', 'f2.id', '=', 'fl.locked_id')
            ->select(
                'f1.id AS invoice_formulir_id',
                'f2.id AS receive_formulir_id',
                'f1.form_date AS invoice_date',
                'f2.form_date AS received_date'
            )
            ->get();

        // update invoice date to have same date as goods receive
        foreach($invoices as $invoice) {
            \DB::table('formulir')
                ->where('id', $invoice->invoice_formulir_id)
                ->update(['form_date' => $invoice->received_date]);
            \DB::table('journal')
                ->where('form_journal_id', $invoice->invoice_formulir_id)
                ->update(['form_date' => $invoice->received_date]);
            \DB::table('inventory')
                ->where('formulir_id', $invoice->invoice_formulir_id)
                ->update(['form_date' => $invoice->received_date]);
        }

        \DB::commit();
    }
}

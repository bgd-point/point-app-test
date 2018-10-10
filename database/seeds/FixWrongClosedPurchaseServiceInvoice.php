<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\PointPurchasing\Models\Service\Invoice;

class FixWrongClosedPurchaseServiceInvoice extends Seeder
{
    private $output;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $invoices = Invoice::joinFormulir()
        ->close()
        ->leftJoin('formulir_lock', 'formulir_lock.locked_id', '=', 'formulir.id')
        ->whereNull('formulir_lock.locked_id')
        ->get();

        foreach($invoices as $key=>$invoice) {
            $url = "//" . 'tenant' . "." . ENV('SERVER_DOMAIN');
            
            $model = $invoice->formulir->formulirable_type;
            $url .= $model::showUrl($invoice->formulir->formulirable_id);

            $formulir = Formulir::find($invoice->formulir_id);
            $formulir->form_status = -1;
            $formulir->canceled_at = date('Y-m-d H:i:s');
            $formulir->canceled_by = 1; // user ID padahal yang execute bukan user
            $formulir->save();
    
            FormulirHelper::clearRelation($formulir);
            $this->output->writeln($invoice->formulir->form_number . ' ' . $url);
        }
    }
}

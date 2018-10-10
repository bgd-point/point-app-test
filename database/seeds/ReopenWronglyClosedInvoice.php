<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Symfony\Component\Console\Helper\Table as Table;
use Point\PointPurchasing\Models\Service\Invoice;
use Point\PointPurchasing\Models\Service\PaymentOrderDetail;
use Illuminate\Database\Eloquent\Model\Formulir;

class ReopenWronglyClosedInvoice extends Seeder
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
        $headers = ['No', 'Form ID', 'Form Number', 'Total Invoice', 'Total Payment'];
        $data = [];

        $this->command->info('--- Getting closed Invoice ---');
        $invoices = Invoice::joinFormulir()->close()->notArchived()->get();

        foreach($invoices as $key=>$value) {
            $paymentOrderDetails = PaymentOrderDetail
            ::joinPaymentOrder()
            ->join('formulir', 'point_purchasing_service_payment_order.formulir_id', '=', 'formulir.id')
            ->whereNotNull('formulir.form_number') // not archived
            ->where('formulir.form_status', 1) // closed
            ->where('form_reference_id', $value->formulir_id)
            ->get();

            $total_payment = $paymentOrderDetails->sum('amount');
            if ($total_payment != $value->total) {
                $row = [
                    'no'            => count($data)+1,
                    'form_id'       => $value->formulir_id,
                    'form_number'   => $value->form_number,
                    'total_invoice' => number_format_quantity($value->total),
                    'total_payment' => number_format_quantity($total_payment)
                ];
                array_push($data, $row);
            }
        }

        $this->command->table = new Table($this->output);
        $this->command->table($headers, $data);

        if ($this->command->confirm('Do you wish to reopen these forms?')) {
            $this->command->info('--- Reopening the form ---');
            DB::beginTransaction();

            foreach($data as $value) {
                $formulir = Formulir::find($value['form_id']);
                $formulir->form_status = 0;
                $formulir->save();
                $this->command->info('--- Formulir ID: ' . $value['form_id'] . '. Reopened ---');
            }

            DB::commit();
        }
    }
}

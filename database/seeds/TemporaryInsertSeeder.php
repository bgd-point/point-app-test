<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\PointExpedition\Models\PaymentOrder;
use Point\PointExpedition\Http\Controllers\PaymentOrderApprovalController;

class TemporaryInsertSeeder extends Seeder
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
        $this->output->writeln('<info>--- Inserting Expedition Payment Order to Finance Payment Reference ---</info>');
        $list_payment_order = PaymentOrder::joinFormulir()
                                          ->leftJoin('point_finance_payment_reference AS fpf', 'point_expedition_payment_order.formulir_id', '=', 'fpf.payment_reference_id')
                                          ->whereNull('fpf.payment_reference_id')
                                          ->where('formulir.form_status', 0)
                                          ->where('formulir.approval_status', 1)
                                          ->select('point_expedition_payment_order.*')
                                          ->get();
        $this->output->writeln('<info>--- Found ' . count($list_payment_order) . ' payment order(s) ---</info>');
        foreach ($list_payment_order as $key => $payment_order) {
            $this->output->writeln('<info>--- Inserting formulir(' . $payment_order->formulir_id . ') ---</info>');
            PaymentOrderApprovalController::addPaymentReference($payment_order);
        }
        $this->output->writeln('<info>--- Inserting Expedition Payment Order to Finance Payment Reference Finished ---</info>');
    }
}
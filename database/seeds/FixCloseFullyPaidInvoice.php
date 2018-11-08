<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;

use Point\PointPurchasing\Models\Service\Invoice;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use function GuzzleHttp\json_decode;

class FixCloseFullyPaidInvoice extends Seeder
{
    /**
     * Seeder that only executed once for existing production system
     *
     * @return void
     */

    private $output;

    public function __construct(ConsoleOutput $output)
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
        $problem_invoice = DB::select("SELECT formulir.id, point_purchasing_service_invoice.total, COALESCE(SUM(journal.debit), 0) AS paid
        FROM point_purchasing_service_invoice
        JOIN formulir ON formulir.id = point_purchasing_service_invoice.formulir_id
        LEFT JOIN journal ON point_purchasing_service_invoice.formulir_id = journal.form_reference_id
        WHERE formulir.form_status = 0
        GROUP BY formulir.id, point_purchasing_service_invoice.total
        HAVING total = paid");

        $form_id = array_column($problem_invoice, 'id');
        $form_id = implode(',', $form_id);

        $this->output->writeln('closing form : ' . $form_id);

        DB::statement('UPDATE formulir SET form_status = 1 WHERE id IN (' . $form_id . ')');

        $this->output->writeln('form closed');
    }
}

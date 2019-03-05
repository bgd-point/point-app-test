<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\AllocationReport;
use Point\PointExpedition\Models\PaymentOrder;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\Cash\Cash;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\PaymentCollection;

class Reallocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reallocation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reallocation';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('recalculating');

        $allocationReports = AllocationReport::with('formulir')->get();

        foreach ($allocationReports as $allocationReport) {
            // $this->line('Al Report ' . $allocationReport->id);
            $type = $allocationReport->formulir->formulirable_type;
            if ($type == \Point\PointPurchasing\Models\Inventory\Invoice::class
                || $type ==  \Point\PointPurchasing\Models\Service\Invoice::class
                || $type == Invoice::class
                || $type == \Point\PointSales\Models\Service\Invoice::class) {

                $locks = FormulirLock::where('locked_id', $allocationReport->formulir->id)->get();

                // invoice reference
                foreach ($locks as $lock) {
                    // $this->line($lock->locking->form_number);
                    $type2 = $lock->locking->formulirable_type;
                    if ($type2 == PaymentCollection::class || $type2 == PaymentOrder::class || $type2 == \Point\PointPurchasing\Models\Service\PaymentOrder::class || $type2 == \Point\PointSales\Models\Service\PaymentCollection::class) {

                        $locks2 = FormulirLock::where('locked_id', $lock->locking_id)->get();

                        foreach ($locks2 as $lock2) {
                            // $this->line($lock2->locking->form_number);

                            $cash = Cash::where('formulir_id', $lock2->locking->id)->first();
                            if ($cash) {
                                foreach ($cash->detail as $detail) {
                                    $type3 = $detail->reference_type;
                                    if ($type3 == \Point\PointPurchasing\Models\Inventory\Invoice::class
                                        || $type3 ==  \Point\PointPurchasing\Models\Service\Invoice::class
                                        || $type3 == Invoice::class
                                        || $type3 == \Point\PointSales\Models\Service\Invoice::class) {

                                        $allocation = Allocation::where('name', '=', $allocationReport->allocation->name.' (CASH FLOW)')->first();
                                        if (!$allocation) {
                                            $allocation = new Allocation;
                                            $allocation->name = $allocationReport->allocation->name.' (CASH FLOW)';
                                            $allocation->save();
                                        }

                                        $newReport = AllocationReport::where('formulir_id', '=', $cash->formulir_id)
                                            ->where('allocation_id', $allocation->id)
                                            ->where('amount', $detail->amount)
                                            ->where('notes', $allocationReport->notes)
                                            ->first();

                                        if ($newReport) {
                                            break;
                                        }

                                        $alReport = new AllocationReport;
                                        $alReport->formulir_id = $cash->formulir_id;
                                        $alReport->allocation_id = $allocation->id;
                                        $alReport->amount = $detail->amount;
                                        $alReport->notes = $allocationReport->notes;
                                        $alReport->save();
                                        $this->line('saving');
                                    }
                                }
                            }

                            $bank = Bank::where('formulir_id', $lock2->locking->id)->first();
                            if ($bank) {
                                foreach ($bank->detail as $detail) {
                                    $type3 = $detail->reference_type;
                                    if ($type3 == \Point\PointPurchasing\Models\Inventory\Invoice::class
                                        || $type3 ==  \Point\PointPurchasing\Models\Service\Invoice::class
                                        || $type3 == Invoice::class
                                        || $type3 == \Point\PointSales\Models\Service\Invoice::class) {

                                        $allocation = Allocation::where('name', '=', $allocationReport->allocation->name.' (CASH FLOW)')->first();

                                        if (!$allocation) {
                                            $allocation = new Allocation;
                                            $allocation->name = $allocationReport->allocation->name.' (CASH FLOW)';
                                            $allocation->save();
                                        }

                                        $newReport = AllocationReport::where('formulir_id', '=', $bank->formulir_id)
                                            ->where('allocation_id', $allocation->id)
                                            ->where('amount', $detail->amount)
                                            ->where('notes', $allocationReport->notes)
                                            ->first();

                                        if ($newReport) {
                                            break;
                                        }

                                        $alReport = new AllocationReport;
                                        $alReport->formulir_id = $bank->formulir_id;
                                        $alReport->allocation_id = $allocation->id;
                                        $alReport->amount = $detail->amount;
                                        $alReport->notes = $allocationReport->notes;
                                        $alReport->save();
                                        $this->line('saving');
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $allocation = Allocation::where('name', '=', $allocationReport->allocation->name.' (CASH FLOW)')->first();
                if (!$allocation) {
                    $allocation = new Allocation;
                    $allocation->name = $allocationReport->allocation->name.' (CASH FLOW)';
                    $allocation->save();
                }

                $alReport = new AllocationReport;
                $alReport->formulir_id = $allocationReport->formulir_id;
                $alReport->allocation_id = $allocation->id;
                $alReport->amount = $allocationReport->amount;
                $alReport->notes = $allocationReport->notes;
                $alReport->save();
            }
        }
    }
}

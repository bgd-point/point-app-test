<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\AllocationReport;
use Point\Framework\Models\Master\Person;
use Point\PointExpedition\Models\PaymentOrder;
use Point\PointFinance\Models\Bank\Bank;
use Point\PointFinance\Models\Bank\BankDetail;
use Point\PointFinance\Models\Cash\Cash;
use Point\PointFinance\Models\Cash\CashDetail;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\PaymentCollection;
use Point\PointSales\Models\Sales\Retur;

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

        $alsd = Allocation::find(1);
        if ($alsd) {
            $alsd->updated_at = Carbon::now();
            $alsd->save();
        }

//        AllocationReport::join('formulir','formulir.id', '=', 'allocation_report.formulir_id')
//            ->where('formulir.form_status', '=', -1)
//            ->delete();
//
//        AllocationReport::join('allocation','allocation.id', '=', 'allocation_report.allocation_id')
//            ->where('allocation.name', 'like', '%(CASH FLOW)')
//            ->delete();

//        $allocationReports = AllocationReport::with('formulir')->get();

//        foreach ($allocationReports as $allocationReport) {
//            $this->line('Al Report ' . $allocationReport->id);
//            $type = $allocationReport->formulir->formulirable_type;
//            if ($type == \Point\PointPurchasing\Models\Inventory\Invoice::class
//                || $type ==  \Point\PointPurchasing\Models\Service\Invoice::class
//                || $type == Invoice::class
//                || $type == \Point\PointSales\Models\Service\Invoice::class) {
//
//                $locks = FormulirLock::where('locked_id', $allocationReport->formulir->id)->get();
//
//                // invoice reference
//                foreach ($locks as $lock) {
//                    // $this->line($lock->locking->form_number);
//                    $type2 = $lock->locking->formulirable_type;
//                    if ($type2 == PaymentCollection::class
//                        || $type2 == PaymentOrder::class
//                        || $type2 == \Point\PointPurchasing\Models\Service\PaymentOrder::class
//                        || $type2 == \Point\PointSales\Models\Service\PaymentCollection::class) {
//
//                        $locks2 = FormulirLock::where('locked_id', $lock->locking_id)->get();
//
//                        foreach ($locks2 as $lock2) {
//                            // $this->line($lock2->locking->form_number);
//
//                            $cash = Cash::where('formulir_id', $lock2->locking->id)->first();
//                            if ($cash) {
//                                if ($allocationReport->formulir->formulirable_type == \Point\PointPurchasing\Models\Service\Invoice::class
//                                    || $allocationReport->formulir->formulirable_type ==  \Point\PointSales\Models\Service\Invoice::class) {
//                                    foreach ($cash->detail as $cashDetail) {
//                                        if ($cashDetail->subledger_type == Person::class) {
//                                            $cashDetail->reference_type = $allocationReport->formulir->formulirable_type;
//                                            $cashDetail->reference_id = $allocationReport->formulir->formulirable_id;
//                                            $cashDetail->save();
//                                        }
//                                    }
//                                }
//                                foreach ($cash->detail as $detail) {
//                                    $type3 = $detail->reference_type;
//                                    if ($type3 == \Point\PointPurchasing\Models\Inventory\Invoice::class
//                                        || $type3 ==  \Point\PointPurchasing\Models\Service\Invoice::class
//                                        || $type3 == Invoice::class
//                                        || $type3 == \Point\PointSales\Models\Service\Invoice::class) {
//
//                                        $allocation = Allocation::where('name', '=', $allocationReport->allocation->name.' (CASH FLOW)')->first();
//                                        if (!$allocation) {
//                                            $allocation = new Allocation;
//                                            $allocation->name = $allocationReport->allocation->name.' (CASH FLOW)';
//                                            $allocation->save();
//                                        }
//
//                                        $newReport = AllocationReport::where('formulir_id', '=', $cash->formulir_id)
//                                            ->where('allocation_id', $allocation->id)
//                                            ->where('amount', $detail->amount)
//                                            ->where('notes', $allocationReport->notes)
//                                            ->first();
//
//                                        if ($newReport) {
//                                            break;
//                                        }
//
//                                        $alReport = new AllocationReport;
//                                        $alReport->formulir_id = $cash->formulir_id;
//                                        $alReport->allocation_id = $allocation->id;
//                                        $alReport->amount = $detail->amount;
//                                        $alReport->notes = $allocationReport->notes;
//                                        $alReport->save();
//                                        $this->line('saving');
//                                    }
//                                }
//                            }
//
//                            $bank = Bank::where('formulir_id', $lock2->locking->id)->first();
//                            if ($bank) {
//                                if ($allocationReport->formulir->formulirable_type == \Point\PointPurchasing\Models\Service\Invoice::class
//                                    || $allocationReport->formulir->formulirable_type ==  \Point\PointSales\Models\Service\Invoice::class) {
//                                    foreach ($bank->detail as $bankDetail) {
//                                        if ($bankDetail->subledger_type == Person::class) {
//                                            $bankDetail->reference_type = $allocationReport->formulir->formulirable_type;
//                                            $bankDetail->reference_id = $allocationReport->formulir->formulirable_id;
//                                            $bankDetail->save();
//                                        }
//                                    }
//                                }
//                                foreach ($bank->detail as $detail) {
//                                    $type3 = $detail->reference_type;
//                                    if ($type3 == \Point\PointPurchasing\Models\Inventory\Invoice::class
//                                        || $type3 ==  \Point\PointPurchasing\Models\Service\Invoice::class
//                                        || $type3 == Invoice::class
//                                        || $type3 == \Point\PointSales\Models\Service\Invoice::class) {
//
//                                        $allocation = Allocation::where('name', '=', $allocationReport->allocation->name.' (CASH FLOW)')->first();
//
//                                        if (!$allocation) {
//                                            $allocation = new Allocation;
//                                            $allocation->name = $allocationReport->allocation->name.' (CASH FLOW)';
//                                            $allocation->save();
//                                        }
//
//                                        $newReport = AllocationReport::where('formulir_id', '=', $bank->formulir_id)
//                                            ->where('allocation_id', $allocation->id)
//                                            ->where('amount', $detail->amount)
//                                            ->where('notes', $allocationReport->notes)
//                                            ->first();
//
//                                        if ($newReport) {
//                                            break;
//                                        }
//
//                                        $alReport = new AllocationReport;
//                                        $alReport->formulir_id = $bank->formulir_id;
//                                        $alReport->allocation_id = $allocation->id;
//                                        $alReport->amount = $detail->amount;
//                                        $alReport->notes = $allocationReport->notes;
//                                        $alReport->save();
//                                        $this->line('saving');
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            } else {
//                if ($allocationReport->formulir->formulirable_type != InventoryUsage::class) {
//                    $allocation = Allocation::where('name', '=', $allocationReport->allocation->name.' (CASH FLOW)')->first();
//                    if (!$allocation) {
//                        $allocation = new Allocation;
//                        $allocation->name = $allocationReport->allocation->name.' (CASH FLOW)';
//                        $allocation->save();
//                    }
//
//                    $alReport = new AllocationReport;
//                    $alReport->formulir_id = $allocationReport->formulir_id;
//                    $alReport->allocation_id = $allocation->id;
//                    $alReport->amount = $allocationReport->amount;
//                    $alReport->notes = $allocationReport->notes;
//                    $alReport->save();
//                }
//            }
//        }

//        $allocationReports = AllocationReport::join('allocation','allocation.id', '=', 'allocation_report.allocation_id')
//            ->join('formulir', 'formulir.id', '=', 'allocation_report.formulir_id')
//            ->where('allocation.name', 'like', '%(CASH FLOW)')
//            ->where(function ($q) {
//                $q->where('formulir.formulirable_type', Bank::class)->orWhere('formulir.formulirable_type', Cash::class);
//            })
//            ->select('allocation_report.*')
//            ->with('allocation')
//            ->with('formulir')
//            ->groupBy('allocation_report.formulir_id')
//            ->get();
//
//        foreach ($allocationReports as $allocationReport) {
//            $allocation_id = $allocationReport->allocation_id;
//            $formulir_id = $allocationReport->formulir_id;
//            $this->line($allocationReport->formulir->form_number);
//            AllocationReport::join('allocation','allocation.id', '=', 'allocation_report.allocation_id')
//                ->join('formulir', 'formulir.id', '=', 'allocation_report.formulir_id')
//                ->where('allocation.name', 'like', '%(CASH FLOW)')
//                ->where('formulir.id', $allocationReport->formulir_id)
//                ->select('formulir.*')
//                ->delete();
//            if ($allocationReport->formulir->formulirable_type == Cash::class) {
//                $cashDetails = CashDetail::where('point_finance_cash_id', $allocationReport->formulir->formulirable_id)->with('allocation')->get();
//                foreach ($cashDetails as $detail) {
//                    $type = $detail->reference;
//                    if ($type) {
//                    } else {
//                        $allocation_id = Allocation::where('name', 'like', $detail->allocation->name .' (CASH FLOW)')->first()->id;
//                    }
//                    $alReport = new AllocationReport;
//                    $alReport->formulir_id = $formulir_id;
//                    $alReport->allocation_id = $allocation_id;
//                    $alReport->amount = $detail->cash->payment_flow == 'out' ? $detail->amount * -1 : $detail->amount;
//                    $alReport->notes = $detail->notes_detail;
//                    $alReport->save();
//                }
//            } else if ($allocationReport->formulir->formulirable_type == Bank::class) {
//                $bankDetails = BankDetail::where('point_finance_bank_id', $allocationReport->formulir->formulirable_id)->with('allocation')->get();
//                foreach ($bankDetails as $detail) {
//                    $type = $detail->reference;
//                    if ($type) {
//                    } else {
//                        $allocation_id = Allocation::where('name', 'like', $detail->allocation->name .' (CASH FLOW)')->first()->id;
//                    }
//                    $alReport = new AllocationReport;
//                    $alReport->formulir_id = $formulir_id;
//                    $alReport->allocation_id = $allocation_id;
//                    $alReport->amount = $detail->bank->payment_flow == 'out' ? $detail->amount * -1 : $detail->amount;
//                    $alReport->notes = $detail->notes_detail;
//                    $alReport->save();
//                }
//            }
//        }

//        $allocationReports = AllocationReport::join('allocation','allocation.id', '=', 'allocation_report.allocation_id')
//            ->join('formulir', 'formulir.id', '=', 'allocation_report.formulir_id')
//            ->where('allocation.name', 'like', '%(CASH FLOW)')
//            ->where('allocation.id', '=', 163)
//            ->where('formulir.formulirable_type', Retur::class)
//            ->select('allocation_report.*')
//            ->with('allocation')
//            ->with('formulir')
//            ->get();
//
//        foreach ($allocationReports as $allocationReport) {
//            $allocationReport->amount = abs($allocationReport->amount) * -1;
//            $allocationReport->save();
//        }

        $allocationReports = AllocationReport::join('allocation','allocation.id', '=', 'allocation_report.allocation_id')
            ->join('formulir', 'formulir.id', '=', 'allocation_report.formulir_id')
            ->where('allocation.name', 'like', '%(CASH FLOW)')
            ->where('formulir.formulirable_type', Retur::class)
            ->select('allocation_report.*')
            ->with('allocation')
            ->with('formulir')
            ->get();

        foreach ($allocationReports as $allocationReport) {
            $allocationReport->amount = abs($allocationReport->amount) * -1;
            $allocationReport->save();
        }
    }
}

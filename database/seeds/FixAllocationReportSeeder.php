<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;

class FixAllocationReportSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $reports = \Point\Framework\Models\Master\AllocationReport::join('formulir', 'formulir.id', '=', 'allocation_report.formulir_id')->select('allocation_report.*', 'form_number')->get();

        foreach ($reports as $report) {
            if (str_contains($report->form_number, 'SALES') || str_contains($report->form_number, 'OUT/')) {
                if ($report->amount > 0) {
                    $report->amount *= -1;
                    $report->save();
                }
            } elseif (str_contains($report->form_number, 'PURCHASING') || str_contains($report->form_number, 'IN/')) {
                if ($report->amount < 0) {
                    $report->amount *= -1;
                    $report->save();
                }
            }
        }

        \DB::commit();
    }
}

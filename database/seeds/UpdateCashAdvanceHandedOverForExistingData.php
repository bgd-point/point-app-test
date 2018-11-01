<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;

class UpdateCashAdvanceHandedOverForExistingData extends Seeder
{
    private $output;

    /**
     * UpdateCashAdvanceHandedOverForExistingData constructor.
     * @param ConsoleOutput $output
     */
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
        $this->output->writeln('<info>--- Update handed over of existing cash advance to true ---</info>');

        DB::table('point_finance_cash_advance')->update(['handed_over' => true]);
    }
}

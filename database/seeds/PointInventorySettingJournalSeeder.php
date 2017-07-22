<?php

use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;

class PointInventorySettingJournalSeeder extends Seeder
{
    /**
     * @var Output
     */
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
        $this->output->writeln('<info>--- Point Inventory Setting Journal Seeder Started ---</info>');

        // Transfer Item
        $coa = Coa::where('name', '=', 'Inventory in Transit')->first();
        SettingJournal::insert('point inventory transfer item', 'inventory in transit', $coa ? $coa->id : null);

        // Inventory Usage
        $coa = Coa::where('name', '=', 'Inventory Differences')->first();
        SettingJournal::insert('point inventory usage', 'inventory differences', $coa ? $coa->id : null);

        // Stock Correction
        $coa = Coa::where('name', '=', 'Inventory Differences')->first();
        SettingJournal::insert('point inventory stock correction', 'inventory differences', $coa ? $coa->id : null);

        // Stock Opname
        $coa = Coa::where('name', '=', 'Inventory Differences')->first();
        SettingJournal::insert('point inventory stock opname', 'inventory differences', $coa ? $coa->id : null);

        $this->output->writeln('<info>--- Point Inventory Setting Journal Finished ---</info>');
    }
}

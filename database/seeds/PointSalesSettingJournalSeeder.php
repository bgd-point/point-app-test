<?php

use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointSalesSettingJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
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
        // Point of Sales
        $this->output->writeln('<info>--- Point Sales Setting Journal Seeder Started ---</info>');
        $coa = Coa::where('name', '=', 'income tax payable')->first();
        SettingJournal::insert('point sales pos', 'income tax payable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'sale of goods')->first();
        SettingJournal::insert('point sales pos', 'sale of goods', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'sales discount')->first();
        SettingJournal::insert('point sales pos', 'sales discount', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'cost of sales')->first();
        SettingJournal::insert('point sales pos', 'cost of sales', $coa ? $coa->id : null);
        $this->output->writeln('<info>--- Point Sales Setting Journal Finished ---</info>');

        // Sales (Stock)
        $this->output->writeln('<info>--- Point Sales Setting Journal Indirect Sales Seeder Started ---</info>');
        $coa = Coa::where('name', '=', 'sale of goods')->first();
        SettingJournal::insert('point sales indirect', 'sale of goods', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'income tax payable')->first();
        SettingJournal::insert('point sales indirect', 'income tax payable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'sales discount')->first();
        SettingJournal::insert('point sales indirect', 'sales discount', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'cost of sales')->first();
        SettingJournal::insert('point sales indirect', 'cost of sales', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'account receivable - sales')->first();
        SettingJournal::insert('point sales indirect', 'account receivable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'sales downpayment')->first();
        SettingJournal::insert('point sales indirect', 'sales downpayment', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'expedition income')->first();
        SettingJournal::insert('point sales indirect', 'expedition income', $coa ? $coa->id : null);
        $this->output->writeln('<info>--- Point Sales Setting Journal Indirect Sales Seeder Finished ---</info>');

        // Sales (Non Stock)
        $this->output->writeln('<info>--- Point Sales Setting Journal Service Sales Seeder Started ---</info>');
        $coa = Coa::where('name', '=', 'sale of goods')->first();
        SettingJournal::insert('point sales service', 'sale of goods', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'income tax payable')->first();
        SettingJournal::insert('point sales service', 'income tax payable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'sales discount')->first();
        SettingJournal::insert('point sales service', 'sales discount', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'cost of sales')->first();
        SettingJournal::insert('point sales service', 'cost of sales', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'account receivable - sales')->first();
        SettingJournal::insert('point sales service', 'account receivable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'service income')->first();
        SettingJournal::insert('point sales service', 'service income', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'sales downpayment')->first();
        SettingJournal::insert('point sales service', 'sales downpayment', $coa ? $coa->id : null);
        $this->output->writeln('<info>--- Point Sales Setting Journal Service Sales Seeder Finished ---</info>');
    }
}

<?php

use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;

class PointExpeditionSettingJournalSeeder extends Seeder
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
        $this->output->writeln('<info>--- Point Expedition Setting Journal Seeder Started ---</info>');
        
        $coa = Coa::where('name', '=', 'Expedition Downpayment')->first();
        SettingJournal::insert('point expedition', 'expedition downpayment', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Expedition Cost')->first();
        SettingJournal::insert('point expedition', 'expedition cost', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Account Payable - Expedition')->first();
        SettingJournal::insert('point expedition', 'account payable - expedition', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Income Tax Receivable')->first();
        SettingJournal::insert('point expedition', 'income tax receivable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Expedition Discount')->first();
        SettingJournal::insert('point expedition', 'expedition discount', $coa ? $coa->id : null);
        
        $this->output->writeln('<info>--- Point Expedition Setting Journal Finished ---</info>');
    }
}

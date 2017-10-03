<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Core\Helpers\PermissionHelper;

class PointPurchasingSettingJournalSeeder extends Seeder
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
        $this->output->writeln('<info>--- Point Purchasing Setting Journal Seeder Started ---</info>');

        // PURCHASE INVENTORY
        $coa = Coa::where('name', '=', 'Income Tax Receivable')->first();
        SettingJournal::insert('point purchasing', 'income tax receivable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Account Payable - Purchasing')->first();
        SettingJournal::insert('point purchasing', 'account payable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Purchase Downpayment')->first();
        SettingJournal::insert('point purchasing', 'purchase downpayment', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Expedition Cost')->first();
        SettingJournal::insert('point purchasing', 'expedition cost', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Purchase Discount')->first();
        SettingJournal::insert('point purchasing', 'purchase discount', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Advance to Employees')->first();
        SettingJournal::insert('point purchasing', 'advance to employees', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Inventory Differeces')->first();
        SettingJournal::insert('point purchasing', 'inventory differences', $coa ? $coa->id : null);

        // PURCHASE SERVICE
        $coa = Coa::where('name', '=', 'Service Cost')->first();
        SettingJournal::insert('point purchasing service', 'service cost', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Income Tax Receivable')->first();
        SettingJournal::insert('point purchasing service', 'income tax receivable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Account Payable - Purchasing')->first();
        SettingJournal::insert('point purchasing service', 'account payable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Purchase Downpayment')->first();
        SettingJournal::insert('point purchasing service', 'purchase downpayment', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Purchase Discount')->first();
        SettingJournal::insert('point purchasing service', 'purchase discount', $coa ? $coa->id : null);

        // PURCHASE FIXED ASSETS
        $coa = Coa::where('name', '=', 'Income Tax Receivable')->first();
        SettingJournal::insert('point purchasing fixed assets', 'income tax receivable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Account Payable - Purchasing')->first();
        SettingJournal::insert('point purchasing fixed assets', 'account payable', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Purchase Downpayment')->first();
        SettingJournal::insert('point purchasing fixed assets', 'purchase downpayment', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Expedition Cost')->first();
        SettingJournal::insert('point purchasing fixed assets', 'expedition cost', $coa ? $coa->id : null);
        $coa = Coa::where('name', '=', 'Purchase Discount')->first();
        SettingJournal::insert('point purchasing fixed assets', 'purchase discount', $coa ? $coa->id : null);
        $this->output->writeln('<info>--- Point Purchasing Setting Journal Seeder Finished ---</info>');
    }
}

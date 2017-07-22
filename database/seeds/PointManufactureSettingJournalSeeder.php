<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufactureSettingJournalSeeder extends Seeder
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
        $this->output->writeln('<info>--- Point Manufacture Setting Journal Seeder Started ---</info>');

        $coa = Coa::where('name', '=', 'Work in Process')->first();
        SettingJournal::insert('manufacture process', 'work in process', $coa ? $coa->id : null);

        $this->output->writeln('<info>--- Point Manufacture Setting Journal Finished ---</info>');
    }
}

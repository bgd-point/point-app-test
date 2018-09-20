<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Framework\Models\FormulirNumber;

class TemporaryInsertSeeder extends Seeder
{
    /**
     * Seeder that only executed once for existing production system
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
        $this->output->writeln('<info>--- Insert formulir number for retur pos ---</info>');

        $formulirNumber = new FormulirNumber;
        $formulirNumber->code = 'POS/RETUR/';
        $formulirNumber->name = 'point-sales-pos-retur';
        $formulirNumber->save();

        $this->output->writeln('<info>--- Insert formulir number for retur pos finished ---</info>');
    }
}

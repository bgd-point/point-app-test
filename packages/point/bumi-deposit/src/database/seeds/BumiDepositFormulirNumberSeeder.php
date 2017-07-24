<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class BumiDepositFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('bumi-deposit', 'BUMI-DEPO/');
    }
}

<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class BumiSharesFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('bumi-shares-buy', 'SHARES-BUY/');
        FormulirNumberHelper::create('bumi-shares-sell', 'SHARES-SELL/');
    }
}

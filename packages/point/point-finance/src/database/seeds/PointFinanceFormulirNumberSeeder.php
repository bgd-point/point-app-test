<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointFinanceFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('point-finance-payment-order', 'PP/');
        FormulirNumberHelper::create('point-finance-cash-payment-in', 'CASH-IN/');
        FormulirNumberHelper::create('point-finance-cash-payment-out', 'CASH-OUT/');
        FormulirNumberHelper::create('point-finance-bank-payment-in', 'BANK-IN/');
        FormulirNumberHelper::create('point-finance-bank-payment-out', 'BANK-OUT/');
        FormulirNumberHelper::create('point-finance-cheque-payment-in', 'CHEQUE-IN/');
        FormulirNumberHelper::create('point-finance-cheque-payment-out', 'CHEQUE-OUT/');
    }
}

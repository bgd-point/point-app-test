<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class KspFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('ksp-loan-application', 'KSP/LOAN/');
        FormulirNumberHelper::create('ksp-invoice', 'KSP/INVOICE/');
        FormulirNumberHelper::create('ksp-payment-collection', 'KSP/PC/');
    }
}

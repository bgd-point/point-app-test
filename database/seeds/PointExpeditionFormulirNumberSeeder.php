<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointExpeditionFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('point-expedition-downpayment', 'BE/DP/');
        FormulirNumberHelper::create('point-expedition-order', 'BE/ORDER/');
        FormulirNumberHelper::create('point-expedition-invoice', 'BE/INVOICE/');
        FormulirNumberHelper::create('point-expedition-payment-order', 'BE/PAYMENT/');
    }
}

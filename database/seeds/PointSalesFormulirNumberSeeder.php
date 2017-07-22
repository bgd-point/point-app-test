<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointSalesFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('point-sales-pos-pricing', 'POS-PRICING/');
        FormulirNumberHelper::create('point-sales-pos', 'POS/');

        // Indirect Sales
        FormulirNumberHelper::create('point-sales-downpayment', 'PS/DP/');
        FormulirNumberHelper::create('point-sales-quotation', 'PS/QUOTE/');
        FormulirNumberHelper::create('point-sales-order', 'PS/ORDER/');
        FormulirNumberHelper::create('point-sales-delivery-order', 'PS/DELIVERY/');
        FormulirNumberHelper::create('point-sales-invoice', 'PS/INVOICE/');
        FormulirNumberHelper::create('point-sales-return', 'PS/RETURN/');
        FormulirNumberHelper::create('point-sales-payment-collection', 'PS/COLLECT/');

        // Service Sales
        FormulirNumberHelper::create('point-sales-service-invoice', 'SALES-SERVICE/INVOICE/');
        FormulirNumberHelper::create('point-sales-service-downpayment', 'SALES-SERVICE/DP/');
        FormulirNumberHelper::create('point-sales-service-payment-collection', 'SALES-SERVICE/COLLECT/');
    }
}

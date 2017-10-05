<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointPurchasingFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        // Basic Purchasing
        FormulirNumberHelper::create('point-purchasing-downpayment', 'BP/DP/');
        FormulirNumberHelper::create('point-purchasing-requisition', 'BP/REQUEST/');
        FormulirNumberHelper::create('point-purchasing-order', 'BP/ORDER/');
        FormulirNumberHelper::create('point-purchasing-goods-received', 'BP/RECEIVE/');
        FormulirNumberHelper::create('point-purchasing-invoice', 'BP/INVOICE/');
        FormulirNumberHelper::create('point-purchasing-retur', 'BP/RETUR/');
        FormulirNumberHelper::create('point-purchasing-payment-order', 'BP/PAYMENT/');
        FormulirNumberHelper::create('point-purchasing-cash-advance', 'BP/CASH-ADVANCE/');
        
        // Service Purchasing
        FormulirNumberHelper::create('point-purchasing-service-invoice', 'PURCHASING-SERVICE/INVOICE/');
        FormulirNumberHelper::create('point-purchasing-service-downpayment', 'PURCHASING-SERVICE/DP/');
        FormulirNumberHelper::create('point-purchasing-service-payment-order', 'PURCHASING-SERVICE/ORDER/');

        // Fixed Assets Purchasing
        FormulirNumberHelper::create('point-purchasing-downpayment-fixed-assets', 'PURCHASING-FA/DP/');
        FormulirNumberHelper::create('point-purchasing-requisition-fixed-assets', 'PURCHASING-FA/REQUEST/');
        FormulirNumberHelper::create('point-purchasing-order-fixed-assets', 'PURCHASING-FA/ORDER/');
        FormulirNumberHelper::create('point-purchasing-goods-received-fixed-assets', 'PURCHASING-FA/RECEIVE/');
        FormulirNumberHelper::create('point-purchasing-invoice-fixed-assets', 'PURCHASING-FA/INVOICE/');
        FormulirNumberHelper::create('point-purchasing-retur-fixed-assets', 'PURCHASING-FA/RETUR/');
        FormulirNumberHelper::create('point-purchasing-payment-order-fixed-assets', 'PURCHASING-FA/PAYMENT/');
        FormulirNumberHelper::create('point-purchasing-contract-fixed-assets', 'CONTRACT/FA/');
    }
}

<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointInventoryFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('point-inventory-transfer-item', 'TI/');
        FormulirNumberHelper::create('point-inventory-inventory-usage', 'IU/');
        FormulirNumberHelper::create('point-inventory-stock-correction', 'SC/');
        FormulirNumberHelper::create('point-inventory-stock-opname', 'SO/');
    }
}

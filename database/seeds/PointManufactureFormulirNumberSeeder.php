<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointManufactureFormulirNumberSeeder extends Seeder
{
    public function run()
    {
        FormulirNumberHelper::create('point-manufacture-formula', 'FORMULA/');
        FormulirNumberHelper::create('point-manufacture-input', 'INPUT/');
        FormulirNumberHelper::create('point-manufacture-output', 'OUTPUT/');
    }
}

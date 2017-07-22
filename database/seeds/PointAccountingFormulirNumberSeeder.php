<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class PointAccountingFormulirNumberSeeder extends Seeder
{
    public function run() 
    {
        FormulirNumberHelper::create('point-accounting-memo-journal', 'AJE/');
        FormulirNumberHelper::create('point-accounting-cut-off-account', 'COA/');
        FormulirNumberHelper::create('point-accounting-cut-off-inventory', 'COI/');
        FormulirNumberHelper::create('point-accounting-cut-off-payable', 'COP/');
        FormulirNumberHelper::create('point-accounting-cut-off-receivable', 'COR/');
        FormulirNumberHelper::create('point-accounting-cut-off-fixed-assets', 'COFA/');
    }
}


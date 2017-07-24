<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirNumberHelper;

class FrameworkFormulirNumberTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('formulir_number')->truncate();

        FormulirNumberHelper::create('opening-inventory', 'OI/');
    }
}

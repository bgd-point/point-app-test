<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Formulir;

class FixFormRawNumberSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();
        \Log::info('Fix formulir number seeder started');

        $list_formulir = Formulir::where('form_raw_number', 0)->whereNotNull('form_number')->get();
        foreach ($list_formulir as $formulir) {
            $formulir->form_raw_number = 1;
            $formulir->save();
        }

        \Log::info('Fix formulir number seeder finished');
        \DB::commit();
    }
}

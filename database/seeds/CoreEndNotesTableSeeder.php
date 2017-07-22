<?php

use Illuminate\Database\Seeder;
use Point\Core\Models\EndNotes;

class CoreEndNotesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $features = array(
            'sales quotation',
            'sales order',
            'sales invoice',
            'sales payment collection',
            'sales service invoice',
            'sales service payment collection',
            'purchase requisition',
            'purchase order',
            'purchase invoice',
            'purchase payment order',
            'purchase service invoice',
            'purchase service payment order',
            'expedition order',
            'expedition invoice',
            'expedition payment order',
         );

        foreach ($features as $feature) {
            EndNotes::insert($feature);
        }
    }
}

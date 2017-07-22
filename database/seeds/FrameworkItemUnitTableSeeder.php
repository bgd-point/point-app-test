<?php

use Illuminate\Database\Seeder;

class FrameworkItemUnitTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('item_unit')->truncate();

        DB::table('item_unit')->insert([
            'item_id' => 1,
            'name' => 'Pcs',
            'converter' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item_unit')->insert([
            'item_id' => 1,
            'name' => 'Pack',
            'as_default' => false,
            'converter' => 6,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item_unit')->insert([
            'item_id' => 2,
            'name' => 'Pcs',
            'converter' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}

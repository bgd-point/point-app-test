<?php

use Illuminate\Database\Seeder;

class FrameworkItemTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('item')->truncate();

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-1',
            'name' => 'Item A',
            'account_asset_id' => 5,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-2',
            'name' => 'Item B',
            'account_asset_id' => 5,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;

class FrameworkItemCategoryTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('item_category')->truncate();

        DB::table('item_category')->insert([
            'code' => 'RM',
            'name' => 'Raw Material',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}

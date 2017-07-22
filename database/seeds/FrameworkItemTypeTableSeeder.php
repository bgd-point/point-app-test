<?php

use Illuminate\Database\Seeder;

class FrameworkItemTypeTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('item_type')->truncate();

        DB::table('item_type')->insert(['name' => 'inventory']);
        DB::table('item_type')->insert(['name' => 'fixed-asset']);
    }
}

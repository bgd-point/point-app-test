<?php

use Illuminate\Database\Seeder;

class FrameworkWarehouseTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('warehouse')->truncate();
        DB::table('warehouse')->insert(['id' => 1,'code' => 'GD-001','name' => 'PUSAT', 'created_by' => 1, 'updated_by' => 1]);
    }
}

<?php

use Illuminate\Database\Seeder;

class FrameworkServiceTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service')->truncate();

        DB::table('service')->insert([
            'name' => 'Service Sepeda Motor',
            'price' => 19000,
            'notes' => 'Full time',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('service')->insert([
            'name' => 'Service AC',
            'price' => 10000,
            'notes' => 'Full time',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('service')->insert([
            'name' => 'Service TV',
            'price' => 50000,
            'notes' => 'Full time',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('service')->insert([
            'name' => 'Service Electronik',
            'price' => 80000,
            'notes' => 'All service tools Electronic',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}

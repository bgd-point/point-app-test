<?php

use Illuminate\Database\Seeder;

class FrameworkPersonTypeTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('person_type')->truncate();
        DB::table('person_type')->insert(['code' => 'SUP', 'name' => 'supplier', 'slug' => 'supplier']);
        DB::table('person_type')->insert(['code' => 'CUS', 'name' => 'customer', 'slug' => 'customer']);
        DB::table('person_type')->insert(['code' => 'EMP', 'name' => 'employee', 'slug' => 'employee']);
        DB::table('person_type')->insert(['code' => 'EXP', 'name' => 'expedition', 'slug' => 'expedition']);
    }
}

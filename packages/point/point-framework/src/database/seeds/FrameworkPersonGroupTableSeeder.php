<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class FrameworkPersonGroupTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('person_group')->truncate();
        DB::table('person_group')->insert(['person_type_id' => 1, 'name' => 'NONE', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person_group')->insert(['person_type_id' => 2, 'name' => 'NONE', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person_group')->insert(['person_type_id' => 3, 'name' => 'NONE', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person_group')->insert(['person_type_id' => 4, 'name' => 'NONE', 'created_by' => 1, 'updated_by' => 1]);
    }
}

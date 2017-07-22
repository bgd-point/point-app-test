<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class PointManufactureTruncateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Model::unguard();

        DB::table('point_manufacture_machine')->truncate();
        DB::table('point_manufacture_formula')->truncate();
        DB::table('point_manufacture_material')->truncate();
        DB::table('point_manufacture_input')->truncate();
        DB::table('point_manufacture_output')->truncate();

        //Delete record on shared tables with other features
        DB::table('history')->where('history_table', 'like', 'point_manufacture_machine' . '%')->delete();
        DB::table('formulir')->where('form_number', 'like', 'FORMULA/' . '%')
            ->orWhere('form_number', 'like', 'INPUT/' . '%')
            ->orWhere('form_number', 'like', 'OUTPUT/' . '%')
            ->orWhere('archived', 'like', 'FORMULA/' . '%')
            ->orWhere('archived', 'like', 'INPUT/' . '%')
            ->orWhere('archived', 'like', 'OUTPUT/' . '%')
            ->delete();

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

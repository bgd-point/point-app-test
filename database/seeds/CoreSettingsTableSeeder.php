<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreSettingsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->truncate();

        DB::table('settings')->insert([
            'name' => 'date-input',
            'value' => 'd-m-y',
            'notes' => 'Format date for input form'
        ]);

        DB::table('settings')->insert([
            'name' => 'date-show',
            'value' => 'd M Y',
            'notes' => 'Format date for view'
        ]);

        DB::table('settings')->insert([
            'name' => 'date-moment',
            'value' => 'DD-MM-YY',
            'notes' => 'Format date for view'
        ]);

        DB::table('settings')->insert([
            'name' => 'mouse-select-allowed',
            'value' => 'true',
            'notes' => 'User authorization to use mouse select'
        ]);

        DB::table('settings')->insert([
            'name' => 'right-click-allowed',
            'value' => 'true',
            'notes' => 'User authorization to use right click on mouse'
        ]);

        DB::table('settings')->insert([
            'name' => 'user-change-password-allowed',
            'value' => 'true',
            'notes' => 'User authorization to change password'
        ]);

        DB::table('settings')->insert([
            'name' => 'user-guide-helper',
            'value' => 'true',
            'notes' => 'Helper Guide for user'
        ]);
    }
}

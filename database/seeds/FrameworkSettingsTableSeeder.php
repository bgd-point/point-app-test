<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class FrameworkSettingsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'name' => 'lock-periode',
            'value' => '2015-01-01',
            'notes' => 'Locking periode for uneditable or uncreateable form on specific date'
        ]);
    }
}

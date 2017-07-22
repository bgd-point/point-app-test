<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        
        DB::table('users')->insert([
            'name' => 'system',
            'email' => 'info@point.red',
            'password' => 'THISUSERNOTACCESSABLE2016!',
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BumiDepositTruncateSeeder extends Seeder
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

        DB::table('bumi_deposit')->truncate();
        DB::table('bumi_deposit_bank')->truncate();
        DB::table('bumi_deposit_group')->truncate();
        DB::table('bumi_deposit_category')->truncate();

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

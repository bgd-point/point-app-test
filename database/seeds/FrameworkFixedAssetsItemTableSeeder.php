<?php

use Illuminate\Database\Seeder;

class FrameworkFixedAssetsItemTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fixed_assets_item')->truncate();

        DB::table('fixed_assets_item')->insert([
            'account_asset_id' => 24,
            'name' => 'Laptop Acer Aspire 3450',
            'code' => 'FA-001',
            'useful_life' => '10',
            'salvage_value' => '1000000',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('fixed_assets_item')->insert([
            'account_asset_id' => 16,
            'name' => 'Kijang Inova',
            'code' => 'FA-002',
            'useful_life' => '10',
            'salvage_value' => '10000000',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}

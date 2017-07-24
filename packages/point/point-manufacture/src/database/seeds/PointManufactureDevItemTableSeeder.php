<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufactureDevItemTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $output = new Output;

        $output->writeln('<info>--- Item Seeder Started ---</info>');

        DB::table('item')->truncate();

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-1',
            'name' => 'Batu krikil',
            'account_asset_id' => Coa::where('name', '=', 'Raw Material')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-2',
            'name' => 'Pasir',
            'account_asset_id' => Coa::where('name', '=', 'Raw Material')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-3',
            'name' => 'Semen',
            'account_asset_id' => Coa::where('name', '=', 'Raw Material')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-4',
            'name' => 'Batu bata',
            'account_asset_id' => Coa::where('name', '=', 'Raw Material')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-5',
            'name' => 'Kapur',
            'account_asset_id' => Coa::where('name', '=', 'Raw Material')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-6',
            'name' => 'Lem Kayu',
            'account_asset_id' => Coa::where('name', '=', 'Raw Material')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 1,
            'code' => 'RM-7',
            'name' => 'Besi Ulir',
            'account_asset_id' => Coa::where('name', '=', 'Raw Material')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 2,
            'code' => 'FG-1',
            'name' => 'Kuas',
            'account_asset_id' => Coa::where('name', '=', 'Finished Goods')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 2,
            'code' => 'FG-2',
            'name' => 'Cat Tembok',
            'account_asset_id' => Coa::where('name', '=', 'Finished Goods')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 2,
            'code' => 'FG-3',
            'name' => 'Cat anti air',
            'account_asset_id' => Coa::where('name', '=', 'Finished Goods')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item')->insert([
            'item_type_id' => 1,
            'item_category_id' => 2,
            'code' => 'FG-4',
            'name' => 'Besi Hollow',
            'account_asset_id' => Coa::where('name', '=', 'Finished Goods')->first()->id,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $output->writeln('<info>--- Item Seeder Finished ---</info>');
    }
}

<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufactureDevItemCategorySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $output = new Output;

        $output->writeln('<info>--- Item Category Seeder Started ---</info>');

        DB::table('item_category')->truncate();

        DB::table('item_category')->insert([
            'code' => 'RM',
            'name' => 'Raw Material',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('item_category')->insert([
            'code' => 'FG',
            'name' => 'Finished Goods',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $output->writeln('<info>--- Item Category Seeder Finished ---</info>');
    }
}

<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufactureDevProcessTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $output = new Output;

        $output->writeln('<info>--- Process Seeder Started ---</info>');

        DB::table('point_manufacture_process')->truncate();

        DB::table('point_manufacture_process')->insert([
            'name' => 'Preparation',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $output->writeln('<info>--- Process Seeder Finished ---</info>');
    }
}

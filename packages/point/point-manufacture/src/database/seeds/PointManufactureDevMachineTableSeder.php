<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufactureDevMachineTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $output = new Output;

        $output->writeln('<info>--- Machine Seeder Started ---</info>');

        DB::table('point_manufacture_machine')->truncate();

        DB::table('point_manufacture_machine')->insert([
            'code' => 'MC-01',
            'name' => 'Japan Gear',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('point_manufacture_machine')->insert([
            'code' => 'MC-02',
            'name' => 'Steel of Dutch',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        DB::table('point_manufacture_machine')->insert([
            'code' => 'MC-03',
            'name' => 'China Craft',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $output->writeln('<info>--- Machine Seeder Finished ---</info>');
    }
}

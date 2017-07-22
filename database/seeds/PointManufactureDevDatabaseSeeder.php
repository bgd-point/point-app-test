<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufactureDevDatabaseSeeder extends Seeder
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
        $output = new Output;

        $output->writeln('<info>start manufacture dev seeder</info>');

        // seed dummy data
        $this->call(PointManufactureDevItemCategorySeeder::class);
        $this->call(PointManufactureDevItemTableSeeder::class);
        $this->call(PointManufactureDevItemUnitTableSeeder::class);
        $this->call(PointManufactureDevMachineTableSeeder::class);
        $this->call(PointManufactureDevProcessTableSeeder::class);

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

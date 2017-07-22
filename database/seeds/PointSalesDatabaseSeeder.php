<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointSalesDatabaseSeeder extends Seeder
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
        $output->writeln('<info>start point sales seeder</info>');
        $this->call(PointSalesPermissionSeeder::class);
        $this->call(PointSalesFormulirNumberSeeder::class);
        $this->call(PointSalesSettingJournalSeeder::class);
        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

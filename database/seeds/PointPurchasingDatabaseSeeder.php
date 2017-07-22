<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointPurchasingDatabaseSeeder extends Seeder
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

        $output->writeln('<info>start point purchasing seeder</info>');
        $this->call(PointPurchasingPermissionSeeder::class);
        $this->call(PointPurchasingFormulirNumberSeeder::class);
        $this->call(PointPurchasingSettingJournalSeeder::class);

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

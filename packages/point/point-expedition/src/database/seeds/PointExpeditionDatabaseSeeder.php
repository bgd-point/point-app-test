<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointExpeditionDatabaseSeeder extends Seeder
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
        $this->call(PointExpeditionPermissionSeeder::class);
        $this->call(PointExpeditionFormulirNumberSeeder::class);
        $this->call(PointExpeditionSettingJournalSeeder::class);
        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

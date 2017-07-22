<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class BumiDepositDatabaseSeeder extends Seeder
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

        $output->writeln('<info>start basic finance seeder</info>');
        $this->call(BumiDepositPermissionSeeder::class);
        $this->call(BumiDepositCategorySeeder::class);
        $this->call(BumiDepositFormulirNumberSeeder::class);

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

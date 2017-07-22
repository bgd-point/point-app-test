<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Point\Core\Models\User;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class CoreDevDatabaseSeeder extends Seeder
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

        $user = User::where('name', '=', 'admin')->orWhere('email', '=', 'admin@point.red')->first();
        if (!$user) {
            $user = new User;
            $user->name = 'admin';
            $user->email = 'admin@point.red';
            $user->password = bcrypt('secret2016');
            $user->save();
        }
        $user->attachRole(1);

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

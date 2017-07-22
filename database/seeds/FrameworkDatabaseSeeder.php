    <?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class FrameworkDatabaseSeeder extends Seeder
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

        $this->call(FrameworkPermissionTableSeeder::class);
        $this->call(FrameworkSettingsTableSeeder::class);
        $this->call(FrameworkCoaTableSeeder::class);
        $this->call(FrameworkSettingJournalSeeder::class);
        $this->call(FrameworkItemTypeTableSeeder::class);
        $this->call(FrameworkAllocationTableSeeder::class);
        $this->call(FrameworkPersonTypeTableSeeder::class);
        $this->call(FrameworkPersonGroupTableSeeder::class);
        $this->call(FrameworkFormulirNumberTableSeeder::class);

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

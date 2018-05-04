<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class FrameworkDevDatabaseSeeder extends Seeder
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

        $this->call(FrameworkWarehouseTableSeeder::class);
        $this->call(FrameworkItemTableSeeder::class);
        $this->call(FrameworkItemUnitTableSeeder::class);

        // supplier
        DB::table('person')->insert(['person_type_id' => 1, 'person_group_id' => 1, 'code' => 'SUP-1','name' => 'Andi', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person')->insert(['person_type_id' => 1, 'person_group_id' => 1, 'code' => 'SUP-2','name' => 'Budi', 'created_by' => 1, 'updated_by' => 1]);

        // customer
        DB::table('person')->insert(['person_type_id' => 2, 'person_group_id' => 2, 'code' => 'CUS-1','name' => 'Charles', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person')->insert(['person_type_id' => 2, 'person_group_id' => 2, 'code' => 'CUS-2','name' => 'Dedi', 'created_by' => 1, 'updated_by' => 1]);

        // employee
        DB::table('person')->insert(['person_type_id' => 3, 'person_group_id' => 3, 'code' => 'EMP-1','name' => 'Erhan', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person')->insert(['person_type_id' => 3, 'person_group_id' => 3, 'code' => 'EMP-2','name' => 'Fahmawati', 'created_by' => 1, 'updated_by' => 1]);

        // expedition
        DB::table('person')->insert(['person_type_id' => 4, 'person_group_id' => 4, 'code' => 'EXB-1','name' => 'Garru', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person')->insert(['person_type_id' => 4, 'person_group_id' => 4, 'code' => 'EXB-2','name' => 'H. Lulung', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person')->insert(['person_type_id' => 4, 'person_group_id' => 4, 'code' => 'EXJ-1','name' => 'Irma', 'created_by' => 1, 'updated_by' => 1]);
        DB::table('person')->insert(['person_type_id' => 4, 'person_group_id' => 4, 'code' => 'EXJ-2','name' => 'Juan', 'created_by' => 1, 'updated_by' => 1]);

        // user warehouse
        DB::table('user_warehouse')->insert(['user_id' => 2, 'warehouse_id' => 1]);

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

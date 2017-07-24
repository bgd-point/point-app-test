<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Allocation;

class FrameworkAllocationTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('allocation')->truncate();

        if (! Allocation::where('name', '=', 'Without Allocation')->first()) {
            DB::table('allocation')->insert(['name' => 'Without Allocation']);
        }
    }
}

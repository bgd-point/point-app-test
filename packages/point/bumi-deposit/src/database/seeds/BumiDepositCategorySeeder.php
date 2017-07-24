<?php

use Illuminate\Database\Seeder;
use Point\BumiDeposit\Models\DepositCategory;

class BumiDepositCategorySeeder extends Seeder
{
    public function run()
    {
        $this->create(['DEPOSITO', 'REKSADANA', 'ASURANSI', 'MTN', 'REPO', 'SURAT BERHARGA', 'OBLIGASI', 'TABUNGAN']);
    }

    private function create($list_name = [])
    {
        foreach ($list_name as $name) {
            if (! $this->exist($name)) {
                DB::table('bumi_deposit_category')->insert(
                    ['name' => $name]
                );
            }
        }
    }

    private function exist($name)
    {
        return DepositCategory::where('name', '=', $name)->first();
    }
}

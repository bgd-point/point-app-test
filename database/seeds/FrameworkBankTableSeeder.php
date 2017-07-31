<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\MasterBank;

class FrameworkBankTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('bank')->truncate();
        
        $data = array('BCA', 'BRI', 'BNI', 'BTPN');
        for ($i=0; $i < count($data); $i++) { 
            $bank = new MasterBank();
            $bank->name = $data[$i];
            $bank->created_by = 1;
            $bank->update_by = 1;
            $bank->save();
        }
    }
}

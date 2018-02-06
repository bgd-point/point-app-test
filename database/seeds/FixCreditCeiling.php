<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\AllocationHelper;

class FixCreditCeiling extends Seeder
{
    public function run()
    {
        \DB::statement('ALTER TABLE person ADD credit_ceiling DECIMAL(16,4) DEFAULT 0');
    }
}

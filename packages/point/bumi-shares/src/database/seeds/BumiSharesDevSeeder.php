<?php

use Illuminate\Database\Seeder;
use Point\BumiShares\Models\Broker;
use Point\BumiShares\Models\Owner;
use Point\BumiShares\Models\OwnerGroup;
use Point\BumiShares\Models\Shares;
use Point\Framework\Helpers\FormulirNumberHelper;

class BumiSharesDevSeeder extends Seeder
{
    public function run()
    {
        $broker = new Broker;
        $broker->name = 'Broker A';
        $broker->buy_fee = 0.2;
        $broker->sales_fee = 0.3;
        $broker->created_by = 1;
        $broker->updated_by = 1;
        $broker->save();

        $owner = new Owner;
        $owner->name = 'Owner A';
        $owner->created_by = 1;
        $owner->updated_by = 1;
        $owner->save();

        $owner_group = new OwnerGroup;
        $owner_group->name = 'Group A';
        $owner_group->created_by = 1;
        $owner_group->updated_by = 1;
        $owner_group->save();

        $shares = new Shares;
        $shares->name = 'Shares A';
        $shares->created_by = 1;
        $shares->updated_by = 1;
        $shares->save();
    }
}

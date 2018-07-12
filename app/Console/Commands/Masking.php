<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Core\Models\User;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;

class Masking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:masking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Masking Data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (Item::all() as $item) {
            $item->name = 'Item' . $item->id .  rand(1000,9999);
            $item->save();
        }

        foreach (Person::all() as $person) {
            $person->name = 'Person' . $person->id .  rand(1000,9999);
            $person->address = '';
            $person->email = '';
            $person->phone = '';
            $person->save();
        }

        foreach (User::all() as $user) {
            $user->name = 'User' . $user->id .  rand(1000,9999);
            $user->email = 'email' . $user->id . '@ran.com';
            $user->password = bcrypt('12341234');
            $user->save();
        }
    }
}

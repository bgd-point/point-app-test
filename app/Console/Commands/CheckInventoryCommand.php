<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Inventory;

class CheckInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:check-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Inventory';

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
        $inventories = Inventory::all();

        foreach ($inventories as $inventory) {
            if ($inventory->formulir->form_number == null) {
                $this->line('ketchup ' . $inventory->formulir->archived);
                $inventory->delete();
            }
        }
    }
}

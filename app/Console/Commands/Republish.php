<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Republish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:republish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Republish package';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('vendor:publish', ['--provider' => 'Point\\Core\\CoreServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\Framework\\FrameworkServiceProvider', '--tag' => ['setup'], '--force' => true]);

        // ADD PLUGINS SETUP HERE
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointInventory\\PointInventoryServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointPurchasing\\PointPurchasingServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointSales\\PointSalesServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointExpedition\\PointExpeditionServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointManufacture\\PointManufactureServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointFinance\\PointFinanceServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointAccounting\\PointAccountingServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\BumiDeposit\\BumiDepositServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\BumiShares\\BumiSharesServiceProvider', '--tag' => ['setup'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\Ksp\\KspServiceProvider', '--tag' => ['setup'], '--force' => true]);
    }
}

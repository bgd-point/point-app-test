<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:reset-menu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset menu';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('vendor:publish', ['--provider' => 'Point\\Core\\CoreServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\Framework\\FrameworkServiceProvider', '--tag' => ['menus'], '--force' => true]);

        // ADD PLUGINS SETUP HERE
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointInventory\\PointInventoryServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointPurchasing\\PointPurchasingServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointSales\\PointSalesServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointExpedition\\PointExpeditionServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointManufacture\\PointManufactureServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointFinance\\PointFinanceServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\PointAccounting\\PointAccountingServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\BumiDeposit\\BumiDepositServiceProvider', '--tag' => ['menus'], '--force' => true]);
        Artisan::call('vendor:publish', ['--provider' => 'Point\\BumiShares\\BumiSharesServiceProvider', '--tag' => ['menus'], '--force' => true]);

    }
}

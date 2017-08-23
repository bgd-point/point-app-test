<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Rejournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:rejournal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rejournal data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('db:seed', ['--force' => true, '--class' => 'RejournalPurchasingAndExpeditionSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'RejournalStockCorrectionSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'RejournalCutoffSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'RejournalSalesSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'RejournalCashBankSeeder']);
    }
}

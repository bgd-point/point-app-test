<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Formulir;
use Symfony\Component\Process\Process;

class MethodChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:methodChecker {method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'method checker in model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $list_formulir = Formulir::select('formulirable_type')->groupBy('formulirable_type')->get();
        foreach ($list_formulir as $formulir) {
            $check = method_exists($formulir->formulirable_type, $this->argument('method'));
            if (!$check) {
                $this->comment($formulir->formulirable_type);
                \Log::info($formulir->formulirable_type);
            }
        }
    }
}
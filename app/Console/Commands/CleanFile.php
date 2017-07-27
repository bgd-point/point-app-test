<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CleanFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:clean-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clean untracked file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('clean untracked file');
        $process = new Process('git clean -f -d');
        $process->setPTY(true);
        $process->setTimeout(null);
        $process->run();

        echo $process->getErrorOutput();
    }
}

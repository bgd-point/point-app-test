<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class UpdatePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:update-package {package_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update package';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $package_name = $this->argument('package_name');

        if($package_name == 'all') {
            $this->updateAllPackage();
        } else {
            $this->updatePackage($package_name);
        }
    }

    private function updatePackage($package_name)
    {
        $this->comment('start update '.$package_name.' package');
        $process = new Process('git checkout master;git pull origin master;');
        $process->setPTY(true);
        $process->setTimeout(null);
        $process->setWorkingDirectory('packages/point/'.$package_name);
        $process->run();

        echo $process->getErrorOutput();
    }

    private function updateAllPackage()
    {
        $registered_package = [
            'point-core',
            'point-framework',
            'point-inventory',
//            'point-purchasing',
            'point-sales',
            'point-expedition',
            'point-manufacture',
            'point-finance',
            'point-accounting'
        ];

        for($i=0;$i < count($registered_package);$i++) {
            $this->comment('start update '.$registered_package[$i].' package');
            $process = new Process('git checkout master;git pull origin master;');
            $process->setPTY(true);
            $process->setTimeout(null);
            $process->setWorkingDirectory('packages/point/'.$registered_package[$i]);
            $process->run();

            echo $process->getErrorOutput();
        }
    }
}

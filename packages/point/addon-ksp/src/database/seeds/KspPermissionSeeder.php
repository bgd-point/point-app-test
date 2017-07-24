<?php

use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;

use Point\Core\Helpers\PermissionHelper;

class KspPermissionSeeder extends Seeder
{
    /**
     * @var Output
     */
    private $output;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    
    public function run()
    {
        $this->output->writeln('<info>--- Permission Ksp Seeder Started ---</info>');

        $group = 'KSP';

        PermissionHelper::create('KSP', ['menu'], $group);
        PermissionHelper::create('KSP LOAN APPLICATION', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('KSP INVOICE', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('KSP PAYMENT COLLECTION', ['create', 'read', 'update', 'delete', 'approval'], $group);

        $this->output->writeln('<info>--- Permission Ksp Seeder Finished ---</info>');
    }
}

<?php

use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufacturePermissionSeeder extends Seeder
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
        $this->output->writeln('<info>--- Permission Seeder Started ---</info>');

        $this->manufacture();
        $this->output->writeln('<info>updated manufacture permission</info>');

        $this->output->writeln('<info>--- Permission Seeder Finished ---</info>');
    }

    private function manufacture()
    {
        $group = 'POINT MANUFACTURE';

        PermissionHelper::create('POINT MANUFACTURE MACHINE', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('POINT MANUFACTURE PROCESS', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT MANUFACTURE FORMULA', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT MANUFACTURE INPUT', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('POINT MANUFACTURE OUTPUT', ['create', 'read', 'update', 'delete'], $group);
    }
}

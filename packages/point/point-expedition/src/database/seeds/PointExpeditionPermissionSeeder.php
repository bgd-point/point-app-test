<?php

use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointExpeditionPermissionSeeder extends Seeder
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
        $this->output->writeln('<info>--- Permission Seeder Expedision Started ---</info>');

        $group = 'POINT Expedition';

        PermissionHelper::create('POINT Expedition ORDER', ['create', 'read', 'update', 'delete', 'export', 'approval'], $group);
        PermissionHelper::create('POINT Expedition DOWNPAYMENT', ['create', 'read', 'update', 'delete', 'export', 'approval'], $group);
        PermissionHelper::create('POINT Expedition INVOICE', ['create', 'read', 'update', 'delete', 'export', 'approval'], $group);
        PermissionHelper::create('POINT Expedition PAYMENT ORDER', ['create', 'read', 'update', 'delete', 'export', 'approval'], $group);

        $this->output->writeln('<info>--- Permission Seeder Expedition Finished ---</info>');
    }
}

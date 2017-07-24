<?php

use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;

use Point\Core\Helpers\PermissionHelper;

class PointInventoryPermissionSeeder extends Seeder
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
        $this->output->writeln('<info>--- Permission Seeder Inventory Started ---</info>');

        $group = 'POINT INVENTORY';
        PermissionHelper::create('POINT INVENTORY TRANSFER ITEM', ['create', 'read', 'update', 'delete', 'approval', 'export'], $group);
        PermissionHelper::create('POINT INVENTORY STOCK CORRECTION', ['create', 'read', 'update', 'delete', 'approval', 'export'], $group);
        PermissionHelper::create('POINT INVENTORY USAGE', ['create', 'read', 'update', 'delete', 'approval', 'export'], $group);
        PermissionHelper::create('POINT INVENTORY STOCK OPNAME', ['create', 'read', 'update', 'delete', 'approval'], $group);

        $this->output->writeln('<info>--- Permission Seeder Inventory Finished ---</info>');
    }
}

<?php

use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Illuminate\Database\Seeder;

use Point\Core\Helpers\PermissionHelper;

class BumiSharesPermissionSeeder extends Seeder
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
        $this->output->writeln('<info>--- Permission Bumi Shares Seeder Started ---</info>');

        $group = 'BUMI SHARES';

        PermissionHelper::create('BUMI SHARES', ['menu', 'create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI SHARES BROKER', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI SHARES OWNER', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI SHARES OWNER GROUP', ['create', 'read', 'update', 'delete'], $group);
        PermissionHelper::create('BUMI SHARES BUY', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('BUMI SHARES SELL', ['create', 'read', 'update', 'delete', 'approval'], $group);
        PermissionHelper::create('BUMI SHARES REPORT', ['read', 'export'], $group);

        $this->output->writeln('<info>--- Permission Bumi Shares Seeder Finished ---</info>');
    }
}

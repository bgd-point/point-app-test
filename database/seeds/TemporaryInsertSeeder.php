<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\Core\Helpers\PermissionHelper;
use Point\Core\Models\Master\PermissionRole;
use Point\Core\Models\Master\Permission;

class TemporaryInsertSeeder extends Seeder
{
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
        $this->output->writeln('<info>--- Inserting export inventory value permission ---</info>');

        PermissionHelper::create('INVENTORY VALUE REPORT', ['export'], 'INVENTORY');
        
        $permission_export = Permission::where('slug', 'export.inventory.value.report')->first();
        
        $permission_role = new PermissionRole;

        $permission_role->permission_id = $permission_export->id;
        $permission_role->role_id = 1;

        $permission_role->save();

        $this->output->writeln('<info>--- Insert export inventory value permission finished ---</info>');

    }
}
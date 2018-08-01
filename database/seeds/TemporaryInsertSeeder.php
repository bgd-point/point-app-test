<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;
use Point\PointExpedition\Models\PaymentOrder;
use Point\PointExpedition\Http\Controllers\PaymentOrderApprovalController;

class TemporaryInsertSeeder extends Seeder
{
    /**
     * Seeder that only executed once for existing production system
     *
     * @return void
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
        $this->output->writeln('<info>--- Insert master item read cogs and price permission ---</info>');

        $permission = new Permission;
        $permission->name = 'Read COGS ITEM';
        $permission->slug = 'read.cogs.item';
        $permission->group = 'MASTER';
        $permission->type = 'ITEM';
        $permission->action = 'Read COGS';
        $permission->save();

        $permission_export = Permission::where('slug', 'read.cogs.item')->first();
        
        $permission_role = new PermissionRole;
        $permission_role->permission_id = $permission_export->id;
        $permission_role->role_id = 1;
        $permission_role->save();

        $permission = new Permission;
        $permission->name = 'Read PRICE ITEM';
        $permission->slug = 'read.price.item';
        $permission->group = 'MASTER';
        $permission->type = 'ITEM';
        $permission->action = 'Read Price';
        $permission->save();
        
        $permission_export = Permission::where('slug', 'read.price.item')->first();
        
        $permission_role = new PermissionRole;
        $permission_role->permission_id = $permission_export->id;
        $permission_role->role_id = 1;
        $permission_role->save();

        $this->output->writeln('<info>--- Insert master item read cogs and price permission finished ---</info>');
    }
}
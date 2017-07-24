<?php

use Illuminate\Database\Seeder;
use Point\Core\Models\Master\Permission;
use Point\Core\Models\Master\Role;
use Point\Core\Models\User;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class CoreDefaultAdminDatabaseSeeder extends Seeder
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
        $this->output->writeln('<info>--- Core Default Admin Started ---</info>');

        $this->output->writeln('<info>update role administrator to get all access</info>');
        $role = Role::find(1);
        foreach (Permission::all() as $permission) {
            $role->attachPermission($permission);
        }

        $this->output->writeln('<info>attach role administrator to user default</info>');
        $system = User::find(1);
        $system->attachRole(1);
        $this->output->writeln('<info>--- Core Default Admin Finished ---</info>');
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\Permission;
use Point\Core\Models\Master\Role;
use Point\Core\Models\User;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class CorePermissionTableSeeder extends Seeder
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

        DB::table('role_user')->truncate();
        DB::table('roles')->truncate();
        $this->output->writeln('<info>truncate role</info>');

        Role::create(['name' => 'administrator ', 'slug' => 'administrator', 'level' => 1, 'created_by' => 1, 'updated_by' => 1]);

        DB::table('permission_user')->truncate();
        DB::table('permissions')->truncate();
        $this->output->writeln('<info>truncate permission</info>');

        $this->menu();
        $this->output->writeln('<info>updated menu permission</info>');
        $this->master();
        $this->output->writeln('<info>updated master permission</info>');
        $this->facility();
        $this->output->writeln('<info>updated facility permission</info>');

        $this->output->writeln('<info>update role administrator to get all access</info>');
        $role = Role::find(1);
        foreach (Permission::all() as $permission) {
            $role->attachPermission($permission);
        }

        $this->output->writeln('<info>attach role administrator to user default</info>');
        $system = User::find(1);
        $system->attachRole(1);
        $this->output->writeln('<info>--- Permission Seeder Finished ---</info>');
    }

    private function menu()
    {
        $group = 'ACCESS MENU';

        $this->updatePermission('MASTER', ['menu'], $group);
        $this->updatePermission('FACILITY', ['menu'], $group);
    }

    private function updatePermission($property, $permissions, $group)
    {
        foreach ($permissions as $permission) {
            switch ($permission) {
                case 'menu':
                    Permission::create(['name' => 'Menu ' . $property, 'slug' => 'menu.' . str_slug($property, '.'), 'group' => $group, 'type' => '# Menu ' . $property, 'action' => 'Access Menu']);
                    break;
                case 'create':
                    Permission::create(['name' => 'Create ' . $property, 'slug' => 'create.' . str_slug($property, '.'), 'group' => $group, 'type' => $property, 'action' => 'Create']);
                    break;
                case 'read':
                    Permission::create(['name' => 'Read ' . $property, 'slug' => 'read.' . str_slug($property, '.'), 'group' => $group, 'type' => $property, 'action' => 'Read']);
                    break;
                case 'update':
                    Permission::create(['name' => 'Update ' . $property, 'slug' => 'update.' . str_slug($property, '.'), 'group' => $group, 'type' => $property, 'action' => 'Edit']);
                    break;
                case 'delete':
                    Permission::create(['name' => 'Delete ' . $property, 'slug' => 'delete.' . str_slug($property, '.'), 'group' => $group, 'type' => $property, 'action' => 'Delete']);
                    break;
                case 'export':
                    Permission::create(['name' => 'Export ' . $property, 'slug' => 'export.' . str_slug($property, '.'), 'group' => $group, 'type' => $property, 'action' => 'Export']);
                    break;
                case 'approval':
                    Permission::create(['name' => 'Approval ' . $property, 'slug' => 'approval.' . str_slug($property, '.'), 'group' => $group, 'type' => $property, 'action' => 'Approval']);
                    break;
                case 'manage':
                    Permission::create(['name' => 'Manage ' . $property, 'slug' => 'manage.' . str_slug($property, '.'), 'group' => $group, 'type' => $property, 'action' => 'Manage']);
                    break;
            }
        }
    }

    private function master()
    {
        $group = 'MASTER';

        $this->updatePermission('ROLE', ['create', 'read', 'update', 'delete', 'export'], $group);
        $this->updatePermission('USER', ['create', 'read', 'update', 'delete', 'export'], $group);
    }

    private function facility()
    {
        $group = 'FACILITY';

        $this->updatePermission('MONITORING', ['manage'], $group);
    }
}

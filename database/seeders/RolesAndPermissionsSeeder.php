<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Base roles and permissions for the BMS.
     *
     * Permissions are CRUD-granular per module ({module}.view/create/update/delete)
     * rather than one blanket "manage" permission, so a role can, for example, view and
     * update machines without being able to delete them. More modules gain their own
     * CRUD set here as each one is built out (see docs/06-security.md).
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $crudModules = ['buildings', 'floors', 'machines', 'users', 'roles'];
        $actions = ['view', 'create', 'update', 'delete'];

        $permissions = [];
        foreach ($crudModules as $module) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        // Devices expose the plaintext sensor API key when generated/viewed, so this is
        // a separate permission from machines.* CRUD — restricted to roles that actually
        // install/wire up hardware or administer it.
        $permissions[] = 'machines.view-device-key';

        // Permissions themselves are read-only in the UI (a permission only does something
        // once code checks it, so there's no "create"/"update"/"delete" action for one).
        $permissions[] = 'permissions.view';

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $manager    = Role::firstOrCreate(['name' => 'manager']);
        $technician = Role::firstOrCreate(['name' => 'technician']);
        $viewer     = Role::firstOrCreate(['name' => 'viewer']);

        // admin: everything.
        $admin->syncPermissions(Permission::all());

        // manager: full oversight of buildings/floors/machines except deleting them;
        // can view users, roles and permissions but not manage accounts or access control
        // (that's admin-only).
        $manager->syncPermissions([
            'buildings.view', 'buildings.create', 'buildings.update',
            'floors.view', 'floors.create', 'floors.update',
            'machines.view', 'machines.create', 'machines.update',
            'machines.view-device-key',
            'users.view',
            'roles.view',
            'permissions.view',
        ]);

        // technician: field/hardware role — views everything, can update a machine's
        // own record (status, maintenance dates), and is the one wiring up sensors.
        $technician->syncPermissions([
            'buildings.view',
            'floors.view',
            'machines.view', 'machines.update',
            'machines.view-device-key',
        ]);

        // viewer: read-only across the board.
        $viewer->syncPermissions([
            'buildings.view',
            'floors.view',
            'machines.view',
        ]);
    }
}

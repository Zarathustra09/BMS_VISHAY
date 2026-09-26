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
     * More permissions will be added here as each module is built out.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Devices expose the plaintext sensor API key when generated/viewed, so this is
        // restricted to roles that actually install/wire up hardware or administer it.
        $permissions = [
            'machines.view-device-key',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin       = Role::firstOrCreate(['name' => 'admin']);
        $manager     = Role::firstOrCreate(['name' => 'manager']);
        $technician  = Role::firstOrCreate(['name' => 'technician']);
        Role::firstOrCreate(['name' => 'viewer']);

        $admin->syncPermissions(Permission::all());
        $manager->givePermissionTo('machines.view-device-key');
        $technician->givePermissionTo('machines.view-device-key');
    }
}

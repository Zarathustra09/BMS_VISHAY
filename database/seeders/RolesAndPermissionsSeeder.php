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

        $crudModules = ['buildings', 'floors', 'machines', 'machine-types', 'users', 'roles', 'work-orders'];
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

        // Alerts are raised by the system (AlertService::evaluate()), never created or
        // deleted by a user, so this module skips the CRUD set in favor of the two actions
        // a person actually takes on one.
        $permissions[] = 'alerts.view';
        $permissions[] = 'alerts.acknowledge';
        $permissions[] = 'alerts.resolve';

        // A technician needs to change a work order's status (start/complete/cancel it)
        // without holding the broader work-orders.update — which also covers editing the
        // title/description/assignment of *any* work order, not just their own. This
        // narrower permission is what WorkOrderService::updateStatus() checks to decide
        // whether the "only your own" restriction applies (see docs/06-security.md).
        $permissions[] = 'work-orders.update-status';

        // Read-only dashboard over machines' next_maintenance_date — no CRUD of its own
        // (the underlying data is edited on the machine record; work orders are a separate
        // module), so this is a single permission like permissions.view above.
        $permissions[] = 'maintenance.view';

        // Reports are read-only rollups (with CSV export) of data every role can already see
        // piecemeal via alerts.view/work-orders.view/machines.view/maintenance.view — no
        // CRUD of its own.
        $permissions[] = 'reports.view';

        // System-wide configuration (company name, alert recipients, offline/preventive
        // timing, default thresholds, log retention) — deliberately admin-only, not granted
        // to any other role below, unlike every other module here.
        $permissions[] = 'settings.view';
        $permissions[] = 'settings.update';

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $technician = Role::firstOrCreate(['name' => 'technician']);
        $viewer = Role::firstOrCreate(['name' => 'viewer']);

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
            'machine-types.view', 'machine-types.create', 'machine-types.update',
            'users.view',
            'roles.view',
            'permissions.view',
            'alerts.view', 'alerts.acknowledge', 'alerts.resolve',
            'work-orders.view', 'work-orders.create', 'work-orders.update', 'work-orders.update-status',
            'maintenance.view',
            'reports.view',
        ]);

        // technician: field/hardware role — views everything, can update a machine's
        // own record (status, maintenance dates), acts on alerts in the field, and works
        // through their assigned work orders (create/edit/delete stay manager+ — see
        // WorkOrderService::updateStatus() for the "only your own" enforcement).
        $technician->syncPermissions([
            'buildings.view',
            'floors.view',
            'machines.view', 'machines.update',
            'machines.view-device-key',
            'machine-types.view',
            'alerts.view', 'alerts.acknowledge', 'alerts.resolve',
            'work-orders.view', 'work-orders.update-status',
            'maintenance.view',
            'reports.view',
        ]);

        // viewer: the dashboard (needs no permission of its own), machines - everything on a
        // machine except creating one, including its device key - and alerts (view, acknowledge,
        // resolve). floors.view is only here because the machine edit form loads its Floor
        // dropdown from the floors endpoint.
        $viewer->syncPermissions([
            'machines.view', 'machines.update', 'machines.delete',
            'machines.view-device-key',
            'floors.view',
            'alerts.view', 'alerts.acknowledge', 'alerts.resolve',
        ]);
    }
}

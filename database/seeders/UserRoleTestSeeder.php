<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Dev/test-only seeder for exercising the Users and Roles pages under realistic volume.
 *
 * Creates two batches:
 *   1. Normal, single-role users spread evenly across every existing role — enough volume
 *      to actually exercise the users/roles DataTables' paging, search, and role filter
 *      instead of testing against 2-3 rows.
 *   2. A handful of users deliberately assigned MULTIPLE roles at once (via assignRole(),
 *      not syncRoles()) — the app's UI assumes exactly one role per user everywhere
 *      (docs/06-security.md), but Spatie itself allows more, and nothing in the database
 *      layer prevents it. This batch exists to surface what actually happens when that
 *      assumption breaks, e.g.:
 *        - users/_datatable-role-cell shows every role as a badge (handles it fine)
 *        - users/_datatable-actions only reads $user->roles->first() for the Edit modal's
 *          preselected role — editing one of these users and saving will silently collapse
 *          them down to whichever single role was shown, via UserService::update()'s
 *          syncRoles([$data['role']])
 *        - RoleService::getAll()'s users_count is computed per-role via User::role($name)
 *          ->count(), so a multi-role user is counted once per role they hold — the sum of
 *          all roles' counts can exceed the actual distinct user count
 *        - roles.index's "Add Users" picker, roles/show's member list, and the role filter
 *          on users/index all match correctly against any of a user's roles
 *
 * Not wired into DatabaseSeeder — run it on demand against a dev database:
 *   php artisan db:seed --class=UserRoleTestSeeder
 */
class UserRoleTestSeeder extends Seeder
{
    /** How many single-role users to create per existing role. */
    private const USERS_PER_ROLE = 15;

    /** How many deliberately-inconsistent multi-role users to create. */
    private const MULTI_ROLE_USERS = 5;

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $roles = Role::pluck('name');

        if ($roles->isEmpty()) {
            $this->command->warn('No roles found even after seeding — aborting.');

            return;
        }

        $this->seedSingleRoleUsers($roles);
        $this->seedMultiRoleUsers($roles);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Done. Every seeded user logs in with password: password');
    }

    private function seedSingleRoleUsers(Collection $roles): void
    {
        foreach ($roles as $role) {
            User::factory()
                ->count(self::USERS_PER_ROLE)
                ->create()
                ->each(fn (User $user) => $user->assignRole($role));

            $this->command->info(self::USERS_PER_ROLE . " single-role users assigned to \"{$role}\".");
        }
    }

    private function seedMultiRoleUsers(Collection $roles): void
    {
        if ($roles->count() < 2) {
            $this->command->warn('Need at least 2 roles to create multi-role test users — skipping.');

            return;
        }

        for ($i = 1; $i <= self::MULTI_ROLE_USERS; $i++) {
            $picked = $roles->random(min($roles->count(), random_int(2, 3)));

            $user = User::factory()->create([
                'name' => "Multi-Role Test User {$i} (" . $picked->implode(' + ') . ')',
            ]);

            $user->assignRole($picked->all());
        }

        $this->command->info(self::MULTI_ROLE_USERS . ' multi-role test users created — look for "Multi-Role Test User" in the Users list.');
    }
}

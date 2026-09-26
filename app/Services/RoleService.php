<?php

namespace App\Services;

use App\Exceptions\RoleActionException;
use App\Interfaces\RoleInterface;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleService implements RoleInterface
{
    /**
     * The one role that can never be renamed, deleted, or have permissions removed.
     */
    private const PROTECTED_ROLE = 'admin';

    public function getAll(): Collection
    {
        return Role::withCount('permissions')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $role->users_count = User::role($role->name)->count();

                return $role;
            });
    }

    public function findById(int $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(string $name, array $permissionNames): Role
    {
        $role = Role::create(['name' => $name]);
        $role->syncPermissions($permissionNames);
        $this->resetPermissionCache();

        return $role;
    }

    public function update(Role $role, string $name, array $permissionNames, User $actingUser): Role
    {
        if ($role->name === self::PROTECTED_ROLE) {
            throw new RoleActionException('The admin role cannot be modified.');
        }

        if ($actingUser->hasRole($role->name) && !in_array('roles.update', $permissionNames, true)) {
            throw new RoleActionException('You cannot remove your own ability to manage roles.');
        }

        $role->update(['name' => $name]);
        $role->syncPermissions($permissionNames);
        $this->resetPermissionCache();

        return $role->fresh('permissions');
    }

    public function delete(Role $role): bool
    {
        if ($role->name === self::PROTECTED_ROLE) {
            throw new RoleActionException('The admin role cannot be deleted.');
        }

        $userCount = User::role($role->name)->count();

        if ($userCount > 0) {
            throw new RoleActionException(
                "Cannot delete \"{$role->name}\" — {$userCount} user(s) still have this role. Reassign them to a different role first."
            );
        }

        $deleted = $role->delete();
        $this->resetPermissionCache();

        return $deleted;
    }

    public function usersWithRole(Role $role): Collection
    {
        return User::role($role->name)->get();
    }

    public function assignUsers(Role $role, array $userIds, User $actingUser): int
    {
        if (in_array($actingUser->id, $userIds)) {
            throw new RoleActionException('You cannot reassign your own account from here — use the Users page instead.');
        }

        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            $user->syncRoles([$role->name]);
        }

        $this->resetPermissionCache();

        return $users->count();
    }

    private function resetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

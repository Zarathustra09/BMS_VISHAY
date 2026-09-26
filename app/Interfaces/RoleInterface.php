<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

interface RoleInterface
{
    /**
     * Every role with its permission count and user count attached.
     */
    public function getAll(): Collection;

    public function findById(int $id): Role;

    public function create(string $name, array $permissionNames): Role;

    /**
     * @throws \App\Exceptions\RoleActionException if the role is `admin`, or if $actingUser
     *         holds this role and $permissionNames would drop their own `roles.update`.
     */
    public function update(Role $role, string $name, array $permissionNames, User $actingUser): Role;

    /**
     * @throws \App\Exceptions\RoleActionException if the role is `admin`, or if any user
     *         still holds this role.
     */
    public function delete(Role $role): bool;

    public function usersWithRole(Role $role): Collection;

    /**
     * Assign this role to each of the given users, moving them off whatever role they
     * currently hold (a user has exactly one role — see docs/06-security.md).
     *
     * @return int number of users assigned
     *
     * @throws \App\Exceptions\RoleActionException if $actingUser's own id is in $userIds —
     *         reassigning your own account here could strip your own access with no one
     *         left to undo it; use the Users page for that instead.
     */
    public function assignUsers(Role $role, array $userIds, User $actingUser): int;
}

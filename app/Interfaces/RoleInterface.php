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
}

<?php

namespace App\Services;

use App\Enums\AppModule;
use App\Interfaces\PermissionInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionService implements PermissionInterface
{
    private const CRUD_ACTIONS = ['view', 'create', 'update', 'delete'];

    public function getGrouped(): array
    {
        $grouped = [];

        foreach ($this->visiblePermissions() as $permission) {
            [$module, $action] = array_pad(explode('.', $permission->name, 2), 2, 'other');

            if (in_array($action, self::CRUD_ACTIONS, true)) {
                $grouped[$module][$action] = $permission;
            } else {
                $grouped[$module]['other'][] = $permission;
            }
        }

        ksort($grouped);

        return $grouped;
    }

    public function getAllWithRoles(): Collection
    {
        return $this->visiblePermissions(Permission::with('roles'));
    }

    /**
     * Every permission except those owned by a switched-off module (see AppModule), which are
     * hidden from the role matrix and Permissions page — they still exist in the database, so
     * switching the module back on restores them with their role assignments intact.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Permission>|null  $query
     */
    private function visiblePermissions($query = null): Collection
    {
        return ($query ?? Permission::query())
            ->orderBy('name')
            ->get()
            ->reject(fn (Permission $permission) => AppModule::isAbilityDisabled($permission->name))
            ->values();
    }
}

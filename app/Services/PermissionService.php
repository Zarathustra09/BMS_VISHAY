<?php

namespace App\Services;

use App\Interfaces\PermissionInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionService implements PermissionInterface
{
    private const CRUD_ACTIONS = ['view', 'create', 'update', 'delete'];

    public function getGrouped(): array
    {
        $grouped = [];

        foreach (Permission::orderBy('name')->get() as $permission) {
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
        return Permission::with('roles')->orderBy('name')->get();
    }
}

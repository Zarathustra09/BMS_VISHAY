<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface PermissionInterface
{
    /**
     * All permissions grouped by module, e.g.:
     * ['machines' => ['view' => Permission, 'create' => Permission, ..., 'other' => [Permission, ...]]]
     * The four CRUD actions become columns for the role permission matrix; anything else
     * (e.g. machines.view-device-key) is bucketed under 'other' for that module.
     */
    public function getGrouped(): array;

    /**
     * Every permission with its roles eager-loaded, for the read-only permissions list page.
     */
    public function getAllWithRoles(): Collection;
}

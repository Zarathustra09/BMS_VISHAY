<?php

namespace App\Http\Controllers;

use App\Interfaces\PermissionInterface;

class PermissionController extends Controller
{
    public function __construct(protected PermissionInterface $permissions)
    {
        $this->middleware('permission:permissions.view');
    }

    public function index()
    {
        $permissions = $this->permissions->getAllWithRoles();
        $groupedPermissions = $this->permissions->getGrouped();

        return view('permissions.index', compact('permissions', 'groupedPermissions'));
    }
}

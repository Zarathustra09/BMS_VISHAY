<?php

namespace App\Http\Controllers;

use App\Exceptions\RoleActionException;
use App\Interfaces\PermissionInterface;
use App\Interfaces\RoleInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        protected RoleInterface $roles,
        protected PermissionInterface $permissions,
    ) {
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.update')->only(['edit', 'update']);
        $this->middleware('permission:roles.delete')->only(['destroy', 'confirmDelete']);
    }

    public function index()
    {
        $roles = $this->roles->getAll();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $groupedPermissions = $this->permissions->getGrouped();

        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        $role = $this->roles->create($validated['name'], $validated['permissions'] ?? []);

        return redirect()->route('roles.show', $role)
            ->with('success', "Role \"{$role->name}\" created successfully.");
    }

    public function show(Role $role)
    {
        $groupedPermissions = $this->permissions->getGrouped();
        $rolePermissionNames = $role->permissions->pluck('name')->toArray();
        $users = $this->roles->usersWithRole($role);

        return view('roles.show', compact('role', 'groupedPermissions', 'rolePermissionNames', 'users'));
    }

    public function edit(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->route('roles.show', $role)->with('error', 'The admin role cannot be modified.');
        }

        $groupedPermissions = $this->permissions->getGrouped();
        $rolePermissionNames = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissionNames'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $this->validateRole($request, $role);

        try {
            $this->roles->update($role, $validated['name'], $validated['permissions'] ?? [], $request->user());
        } catch (RoleActionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('roles.show', $role)->with('success', 'Role updated successfully.');
    }

    public function confirmDelete(Role $role)
    {
        $users = $this->roles->usersWithRole($role);

        return view('roles.delete', compact('role', 'users'));
    }

    public function destroy(Role $role)
    {
        try {
            $name = $role->name;
            $this->roles->delete($role);
        } catch (RoleActionException $e) {
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        }

        return redirect()->route('roles.index')->with('success', "Role \"{$name}\" deleted successfully.");
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9\-]*$/',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.regex' => 'Role name must be lowercase letters, numbers, and hyphens only (e.g. "shift-supervisor").',
        ]);
    }
}

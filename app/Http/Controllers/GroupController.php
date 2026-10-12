<?php

namespace App\Http\Controllers;

use App\Exceptions\GroupActionException;
use App\Interfaces\GroupInterface;
use App\Interfaces\PermissionInterface;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class GroupController extends Controller
{
    public function __construct(
        protected GroupInterface $groups,
        protected PermissionInterface $permissions,
    ) {
        $this->middleware('permission:groups.view')->only(['index', 'show']);
        $this->middleware('permission:groups.create')->only(['create', 'store']);
        $this->middleware('permission:groups.update')->only(['edit', 'update']);
        $this->middleware('permission:groups.delete')->only(['destroy', 'confirmDelete']);
    }

    public function index()
    {
        $groups = $this->groups->getAll();

        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        return view('groups.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateGroup($request);

        try {
            $group = $this->groups->create($validated, $request->user());
        } catch (GroupActionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('groups.show', $group)
            ->with('success', "Group \"{$group->name}\" created successfully.");
    }

    public function show(Group $group)
    {
        $group = $this->groups->findById($group->id);
        $groupedPermissions = $this->permissions->getGrouped();
        $groupPermissionNames = $group->permissions->pluck('name')->toArray();

        return view('groups.show', compact('group', 'groupedPermissions', 'groupPermissionNames'));
    }

    public function edit(Group $group)
    {
        $group = $this->groups->findById($group->id);

        return view('groups.edit', $this->formData() + ['group' => $group]);
    }

    public function update(Request $request, Group $group)
    {
        $validated = $this->validateGroup($request, $group);

        try {
            $this->groups->update($group, $validated, $request->user());
        } catch (GroupActionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('groups.show', $group)
            ->with('success', "Group \"{$group->name}\" updated successfully.");
    }

    public function confirmDelete(Group $group)
    {
        $machines = $group->machines()->orderBy('name')->get(['id', 'name', 'machine_code']);

        return view('groups.delete', compact('group', 'machines'));
    }

    public function destroy(Group $group)
    {
        try {
            $name = $group->name;
            $this->groups->delete($group);
        } catch (GroupActionException $e) {
            return redirect()->route('groups.index')->with('error', $e->getMessage());
        }

        return redirect()->route('groups.index')->with('success', "Group \"{$name}\" deleted successfully.");
    }

    /**
     * Everything the create/edit form needs besides the group itself. The admin role is only
     * offered to an admin - GroupService refuses it for anyone else anyway.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $isAdmin = auth()->user()->hasRole('admin');

        return [
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
            'roles' => Role::orderBy('name')->when(! $isAdmin, fn ($q) => $q->where('name', '!=', 'admin'))->get(),
            'groupedPermissions' => $this->permissions->getGrouped(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateGroup(Request $request, ?Group $group = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('groups', 'name')->ignore($group?->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'user_ids' => ['array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
    }
}

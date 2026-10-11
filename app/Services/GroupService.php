<?php

namespace App\Services;

use App\Enums\AppModule;
use App\Exceptions\GroupActionException;
use App\Interfaces\GroupInterface;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class GroupService implements GroupInterface
{
    /**
     * Only an admin may hand out (or edit a group holding) this role - otherwise anyone who can
     * edit groups could put themselves in a group that makes them an admin.
     */
    private const ADMIN_ROLE = 'admin';

    public function getAll(): Collection
    {
        return Group::withCount(['users', 'machines', 'roles', 'permissions'])
            ->orderBy('name')
            ->get();
    }

    public function getForSelect(): Collection
    {
        return Group::query()->orderBy('name')->get(['id', 'name']);
    }

    public function findById(int $id): Group
    {
        return Group::with(['users', 'roles', 'permissions', 'machines'])->findOrFail($id);
    }

    public function create(array $data, User $actingUser): Group
    {
        $this->guardAdminRole($data['roles'] ?? [], null, $actingUser);

        return DB::transaction(function () use ($data) {
            $group = Group::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $this->syncRelations($group, $data);

            return $group;
        });
    }

    public function update(Group $group, array $data, User $actingUser): Group
    {
        $this->guardAdminRole($data['roles'] ?? [], $group, $actingUser);

        return DB::transaction(function () use ($group, $data) {
            $group->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $this->syncRelations($group, $data);

            return $group;
        });
    }

    public function delete(Group $group): bool
    {
        $machines = $group->machines()->orderBy('name')->pluck('name');

        if ($machines->isNotEmpty()) {
            throw new GroupActionException(
                "This group is the assignment group of {$machines->count()} machine(s) and can't be deleted yet: "
                .$machines->take(5)->implode(', ').($machines->count() > 5 ? ', …' : '')
                .'. Give those machines another group (or none) first.'
            );
        }

        $deleted = (bool) $group->delete();
        $this->resetPermissionCache();

        return $deleted;
    }

    /**
     * @param  array<int, string>  $requestedRoles
     */
    private function guardAdminRole(array $requestedRoles, ?Group $group, User $actingUser): void
    {
        if ($actingUser->hasRole(self::ADMIN_ROLE)) {
            return;
        }

        $touchesAdmin = in_array(self::ADMIN_ROLE, $requestedRoles, true)
            || ($group !== null && $group->hasRole(self::ADMIN_ROLE));

        if ($touchesAdmin) {
            throw new GroupActionException('Only an administrator can create or change a group that has the admin role.');
        }
    }

    /**
     * Members, roles and direct permissions, replaced wholesale with what the form submitted.
     *
     * The permission matrix never lists a switched-off module's permissions (PermissionService),
     * so they're not in the submitted list - keep whatever the group already holds for them, the
     * same way RoleService::update() does, or saving a group in the lite edition would silently
     * strip them.
     */
    private function syncRelations(Group $group, array $data): void
    {
        $group->users()->sync($data['user_ids'] ?? []);
        $group->syncRoles($data['roles'] ?? []);

        $hiddenHeld = $group->permissions
            ->pluck('name')
            ->filter(fn (string $name) => AppModule::isAbilityDisabled($name))
            ->all();

        $group->syncPermissions(array_values(array_unique(array_merge($data['permissions'] ?? [], $hiddenHeld))));

        $this->resetPermissionCache();
    }

    private function resetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

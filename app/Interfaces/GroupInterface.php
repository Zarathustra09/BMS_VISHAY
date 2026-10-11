<?php

namespace App\Interfaces;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Collection;

interface GroupInterface
{
    /**
     * Every group with its member, machine and role counts attached.
     */
    public function getAll(): Collection;

    /**
     * Just id and name, for the machine form's Assignment Group select.
     */
    public function getForSelect(): Collection;

    public function findById(int $id): Group;

    /**
     * @param  array{name: string, description?: ?string, user_ids?: array<int, int>, roles?: array<int, string>, permissions?: array<int, string>}  $data
     *
     * @throws \App\Exceptions\GroupActionException if $actingUser is not an admin and the group
     *                                              would hold the admin role.
     */
    public function create(array $data, User $actingUser): Group;

    /**
     * @param  array{name: string, description?: ?string, user_ids?: array<int, int>, roles?: array<int, string>, permissions?: array<int, string>}  $data
     *
     * @throws \App\Exceptions\GroupActionException if $actingUser is not an admin and the group
     *                                              holds, or would gain, the admin role.
     */
    public function update(Group $group, array $data, User $actingUser): Group;

    /**
     * @throws \App\Exceptions\GroupActionException if any machine still uses the group as its
     *                                              assignment group.
     */
    public function delete(Group $group): bool;
}

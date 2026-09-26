<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface UserInterface
{
    /**
     * Query for the users DataTable, optionally filtered by role.
     */
    public function getQueryForDataTable(array $filters = []): Builder;

    /**
     * Total user count plus a count per existing role.
     */
    public function getStats(): array;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function updatePassword(User $user, string $password): User;

    public function delete(User $user): bool;
}

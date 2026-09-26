<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserInterface
{
    /**
     * Paginated user list, optionally filtered by search term and/or role.
     */
    public function getAllPaginated(array $filters, int $perPage = 12): LengthAwarePaginator;

    /**
     * Total user count plus a count per existing role.
     */
    public function getStats(): array;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function updatePassword(User $user, string $password): User;

    public function delete(User $user): bool;
}

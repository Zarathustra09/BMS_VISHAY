<?php

namespace App\Services;

use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class UserService implements UserInterface
{
    public function getAllPaginated(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = User::with('roles')->orderBy('name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $query->role($filters['role']);
        }

        return $query->paginate($perPage);
    }

    public function getStats(): array
    {
        $stats = ['total' => User::count()];

        foreach (Role::pluck('name') as $role) {
            $stats[$role] = User::role($role)->count();
        }

        return $stats;
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $user->syncRoles([$data['role']]);

        return $user->fresh();
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->update(['password' => bcrypt($password)]);

        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}

<?php

namespace App\Services;

use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserService implements UserInterface
{
    public function getQueryForDataTable(array $filters = []): Builder
    {
        $query = User::with('roles');

        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $query->role($filters['role']);
        }

        return $query;
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

<?php

namespace App\Http\Controllers;

use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(protected UserInterface $users)
    {
        $this->middleware('permission:users.view')->only(['index']);
        $this->middleware('permission:users.create')->only(['store']);
        $this->middleware('permission:users.update')->only(['update', 'resetPassword']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role']);
        $users = $this->users->getAllPaginated($filters);
        $stats = $this->users->getStats();
        $roles = Role::orderBy('name')->pluck('name');

        return view('users.index', compact('users', 'stats', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(Role::pluck('name'))],
        ]);

        $this->users->create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => ['required', Rule::in(Role::pluck('name'))],
        ]);

        $this->users->update($user, $validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->users->updatePassword($user, $validated['password']);

        return redirect()->route('users.index')->with('success', "Password updated for {$user->name}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $this->users->delete($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
